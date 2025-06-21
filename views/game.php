<?php
require_once '../controllers/UserController.php';
$controller = new UserController();

if(!$controller->isLoggedIn()) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pac-Man</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(120deg, #84fab0 0%, #8fd3f4 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .game-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        #gameCanvas {
            border: 2px solid #333;
            border-radius: 10px;
            background: #000;
            margin: 20px auto;
            display: block;
        }
        .score {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-game {
            margin: 10px;
            padding: 10px 20px;
            border-radius: 20px;
        }
        .controls {
            text-align: center;
            margin-top: 20px;
        }
        .game-over {
            color: red;
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-container">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Bienvenido, <?php echo $_SESSION['username']; ?>!</h2>
                <a href="logout.php" class="btn btn-danger btn-sm">Cerrar Sesión</a>
            </div>
            
            <div class="score">
                Puntuación: <span id="score">0</span>
            </div>
            
            <canvas id="gameCanvas" width="400" height="400"></canvas>
            
            <div class="game-over" id="gameOver">
                ¡Juego Terminado!
            </div>
            
            <div class="text-center">
                <button class="btn btn-primary btn-game" onclick="startGame()">Iniciar Juego</button>
                <button class="btn btn-secondary btn-game" onclick="resetGame()">Reiniciar</button>
            </div>
            
            <div class="controls">
                <p>Usa las flechas del teclado para mover a Pac-Man</p>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const blockSize = 20;
        const gridSize = 20;
        let score = 0;
        let pacman = {};
        let ghosts = [];
        let dots = [];
        let walls = [];
        let d = "";
        let game;
        let gameActive = false;

        // Definir el laberinto (1 = pared, 0 = camino)
        const maze = [
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,1,1,0,0,1,1,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0,1],
            [1,1,1,1,1,0,1,1,1,0,0,1,1,1,0,1,1,1,1,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,1,1,1,1,0,1,1,1,0,0,1,1,1,0,1,1,1,1,1],
            [1,0,0,0,0,0,1,0,0,0,0,0,0,1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,1,1,0,0,1,1,1,0,1,1,1,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,0,1,1,1,0,1,0,1,1,1,1,0,1,0,1,1,1,0,1],
            [1,0,0,0,1,0,1,0,0,0,0,0,0,1,0,1,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1],
            [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]
        ];

        function initGame() {
            // Inicializar Pac-Man en el centro de una casilla
            pacman = {
                x: blockSize * 1.5,
                y: blockSize * 1.5,
                radius: blockSize/2 - 2,
                angle: 0.2,
                speed: 4,
                mouthAngle: 0.2,
                rotation: 0 // 0 = derecha, 1 = abajo, 2 = izquierda, 3 = arriba
            };

            // Inicializar fantasmas centrados en sus casillas
            ghosts = [
                {x: blockSize * 18.5, y: blockSize * 1.5, color: "red", dx: -1, dy: 0},
                {x: blockSize * 1.5, y: blockSize * 18.5, color: "pink", dx: 1, dy: 0},
                {x: blockSize * 18.5, y: blockSize * 18.5, color: "cyan", dx: -1, dy: 0}
            ];

            // Inicializar puntos centrados
            dots = [];
            for(let y = 0; y < gridSize; y++) {
                for(let x = 0; x < gridSize; x++) {
                    if(maze[y][x] === 0) {
                        dots.push({x: x*blockSize + blockSize/2, y: y*blockSize + blockSize/2});
                    }
                }
            }
        }

        function drawPacman() {
            ctx.save();
            ctx.translate(pacman.x, pacman.y);
            
            // Rotar según la dirección
            let rotation = 0;
            switch(d) {
                case "RIGHT": rotation = 0; break;
                case "DOWN": rotation = Math.PI/2; break;
                case "LEFT": rotation = Math.PI; break;
                case "UP": rotation = -Math.PI/2; break;
            }
            ctx.rotate(rotation);

            // Animar la boca
            pacman.mouthAngle = 0.2 + Math.abs(Math.sin(Date.now() * 0.1)) * 0.3;

            ctx.beginPath();
            ctx.arc(0, 0, pacman.radius, pacman.mouthAngle, Math.PI * 2 - pacman.mouthAngle);
            ctx.lineTo(0, 0);
            ctx.fillStyle = "yellow";
            ctx.fill();
            ctx.closePath();
            
            ctx.restore();
        }

        function drawGhosts() {
            ghosts.forEach(ghost => {
                // Cuerpo del fantasma
                ctx.beginPath();
                ctx.arc(ghost.x, ghost.y, pacman.radius, Math.PI, 0, false);
                ctx.lineTo(ghost.x + pacman.radius, ghost.y + pacman.radius);
                
                // Crear el efecto ondulado en la base
                for(let i = 0; i < 3; i++) {
                    const curve = Math.sin(Date.now() * 0.1 + i) * 2;
                    ctx.quadraticCurveTo(
                        ghost.x + pacman.radius * (0.5 - i/2), 
                        ghost.y + pacman.radius + curve,
                        ghost.x + pacman.radius * (-1 + i/2), 
                        ghost.y + pacman.radius
                    );
                }
                
                ctx.lineTo(ghost.x - pacman.radius, ghost.y);
                ctx.fillStyle = ghost.color;
                ctx.fill();
                ctx.closePath();

                // Ojos
                const eyeOffset = pacman.radius * 0.3;
                ctx.fillStyle = "white";
                ctx.beginPath();
                ctx.arc(ghost.x - eyeOffset, ghost.y - eyeOffset, 4, 0, Math.PI * 2);
                ctx.arc(ghost.x + eyeOffset, ghost.y - eyeOffset, 4, 0, Math.PI * 2);
                ctx.fill();
                
                // Pupilas
                ctx.fillStyle = "blue";
                ctx.beginPath();
                ctx.arc(ghost.x - eyeOffset + 2, ghost.y - eyeOffset, 2, 0, Math.PI * 2);
                ctx.arc(ghost.x + eyeOffset + 2, ghost.y - eyeOffset, 2, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        function drawDots() {
            dots.forEach(dot => {
                ctx.beginPath();
                ctx.arc(dot.x, dot.y, 3, 0, Math.PI * 2);
                ctx.fillStyle = "white";
                ctx.fill();
                ctx.closePath();
            });
        }

        function drawMaze() {
            for(let y = 0; y < gridSize; y++) {
                for(let x = 0; x < gridSize; x++) {
                    if(maze[y][x] === 1) {
                        ctx.fillStyle = "blue";
                        ctx.fillRect(x*blockSize, y*blockSize, blockSize, blockSize);
                    }
                }
            }
        }

        function checkCollision(x, y) {
            const gridX = Math.floor(x/blockSize);
            const gridY = Math.floor(y/blockSize);
            return maze[gridY][gridX] === 1;
        }

        function moveGhosts() {
            ghosts.forEach(ghost => {
                const newX = ghost.x + ghost.dx * 2;
                const newY = ghost.y + ghost.dy * 2;

                // Verificar colisiones con las paredes teniendo en cuenta el centro del fantasma
                if(!checkCollision(newX - pacman.radius, newY - pacman.radius) && 
                   !checkCollision(newX + pacman.radius, newY + pacman.radius)) {
                    ghost.x = newX;
                    ghost.y = newY;
                } else {
                    // Cambiar dirección aleatoriamente evitando la dirección actual
                    const possibleDirections = [];
                    const directions = [[1,0], [-1,0], [0,1], [0,-1]];
                    
                    directions.forEach(([dx, dy]) => {
                        const testX = ghost.x + dx * blockSize;
                        const testY = ghost.y + dy * blockSize;
                        if(!checkCollision(testX - pacman.radius, testY - pacman.radius) && 
                           !checkCollision(testX + pacman.radius, testY + pacman.radius)) {
                            possibleDirections.push([dx, dy]);
                        }
                    });

                    if(possibleDirections.length > 0) {
                        const [dx, dy] = possibleDirections[Math.floor(Math.random() * possibleDirections.length)];
                        ghost.dx = dx;
                        ghost.dy = dy;
                    }
                }
            });
        }

        function checkDotCollision() {
            for(let i = dots.length - 1; i >= 0; i--) {
                const dx = dots[i].x - pacman.x;
                const dy = dots[i].y - pacman.y;
                const distance = Math.sqrt(dx*dx + dy*dy);
                
                if(distance < pacman.radius) {
                    dots.splice(i, 1);
                    score += 10;
                    document.getElementById("score").textContent = score;
                }
            }
        }

        function checkGhostCollision() {
            for(let ghost of ghosts) {
                const dx = ghost.x - pacman.x;
                const dy = ghost.y - pacman.y;
                const distance = Math.sqrt(dx*dx + dy*dy);
                
                if(distance < pacman.radius + blockSize/2) {
                    return true;
                }
            }
            return false;
        }

        function draw() {
            ctx.fillStyle = "#000";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            
            drawMaze();
            drawDots();
            drawPacman();
            drawGhosts();
            
            // Mover Pac-Man
            let newX = pacman.x;
            let newY = pacman.y;
            
            if(d === "LEFT") newX -= pacman.speed;
            if(d === "UP") newY -= pacman.speed;
            if(d === "RIGHT") newX += pacman.speed;
            if(d === "DOWN") newY += pacman.speed;
            
            // Verificar colisiones con las paredes teniendo en cuenta el radio de Pac-Man
            if(!checkCollision(newX - pacman.radius, newY - pacman.radius) && 
               !checkCollision(newX + pacman.radius, newY + pacman.radius)) {
                pacman.x = newX;
                pacman.y = newY;
            }
            
            moveGhosts();
            checkDotCollision();
            
            // Verificar fin del juego
            if(checkGhostCollision()) {
                clearInterval(game);
                gameActive = false;
                document.getElementById("gameOver").style.display = "block";
                return;
            }
            
            // Victoria
            if(dots.length === 0) {
                clearInterval(game);
                gameActive = false;
                document.getElementById("gameOver").textContent = "¡Has Ganado!";
                document.getElementById("gameOver").style.color = "green";
                document.getElementById("gameOver").style.display = "block";
                return;
            }
        }

        document.addEventListener("keydown", direction);

        function direction(event) {
            if(!gameActive) return;
            
            let key = event.keyCode;
            if(key === 37) d = "LEFT";
            if(key === 38) d = "UP";
            if(key === 39) d = "RIGHT";
            if(key === 40) d = "DOWN";
        }

        function startGame() {
            if(!gameActive) {
                gameActive = true;
                document.getElementById("gameOver").style.display = "none";
                score = 0;
                document.getElementById("score").textContent = score;
                initGame();
                if(game) clearInterval(game);
                game = setInterval(draw, 50);
            }
        }

        function resetGame() {
            if(game) clearInterval(game);
            gameActive = false;
            document.getElementById("gameOver").style.display = "none";
            document.getElementById("gameOver").textContent = "¡Juego Terminado!";
            document.getElementById("gameOver").style.color = "red";
            ctx.fillStyle = "#000";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            score = 0;
            document.getElementById("score").textContent = score;
            d = "";
        }
    </script>
</body>
</html> 