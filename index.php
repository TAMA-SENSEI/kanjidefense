<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanij Defence</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        #canvas-container {
            position: relative;
            border: 2px solid #333;
            margin: 0 auto;
        }
        canvas {
            display: block;
            background-color: #f5f5f5;
        }
        .controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        button {
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background-color: #45a049;
        }
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        input, select {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        label {
            font-weight: bold;
            font-size: 14px;
        }
        #point-list {
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        .point-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
        }
        .info-panel {
            background-color: #e9f7ef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="controls">
            <div class="control-group">
                <label for="image-select">Choose an image:</label>
                <select id="image-select" onchange="changeImage()">
                    <option value="car">Car</option>
                    <option value="plane">Airplane</option>
                    <option value="rocket">Rocket</option>
                </select>
            </div>
            
            <div class="control-group">
                <label for="speed-input">Animation speed:</label>
                <input type="range" id="speed-input" min="1" max="10" value="5">
            </div>
            
            <div class="control-group">
                <label for="size-input">Image size:</label>
                <input type="range" id="size-input" min="20" max="100" value="50" oninput="updateImageSize()">
            </div>
            
            <div class="control-group">
                <button id="start-btn" onclick="startAnimation()" disabled>Start Animation</button>
                <button id="stop-btn" onclick="stopAnimation()" disabled>Stop Animation</button>
                <button id="reset-btn" onclick="resetCanvas()">Reset Canvas</button>
            </div>
        </div>
        
        <div id="canvas-container">
            <canvas id="main-canvas" width="800" height="500"></canvas>
        </div>
        
        <div class="control-group">
            <label>Path Points:</label>
            <div id="point-list"></div>
            <button id="remove-last-btn" onclick="removeLastPoint()" disabled>Remove Last Point</button>
        </div>
    </div>

    <script>
        // Canvas setup
        const canvas = document.getElementById('main-canvas');
        const ctx = canvas.getContext('2d');
        let canvasRect = canvas.getBoundingClientRect();
        
        // Images
        const images = {
            car: createImage('/api/placeholder/100/50', 'Car'),
            plane: createImage('/api/placeholder/100/50', 'Airplane'),
            rocket: createImage('/api/placeholder/50/100', 'Rocket')
        };
        
        let currentImage = 'car';
        let imageWidth = 50;
        let imageHeight = 25;
        
        // Animation variables
        let points = [];
        let animating = false;
        let animationId = null;
        let currentPointIndex = 0;
        let progress = 0;
        let speed = 0.05;
        
        // Initialize canvas
        function init() {
            canvas.addEventListener('click', handleCanvasClick);
            updateSpeedFromSlider();
            document.getElementById('speed-input').addEventListener('input', updateSpeedFromSlider);
            updateImageSize();
            drawCanvas();
        }
        
        function createImage(src, alt) {
            const img = new Image();
            img.src = src;
            img.alt = alt;
            return img;
        }
        
        function updateSpeedFromSlider() {
            const speedSlider = document.getElementById('speed-input');
            speed = speedSlider.value / 100;
        }
        
        function updateImageSize() {
            const sizeSlider = document.getElementById('size-input');
            const size = parseInt(sizeSlider.value);
            
            if (currentImage === 'rocket') {
                imageWidth = size / 2;
                imageHeight = size;
            } else {
                imageWidth = size;
                imageHeight = size / 2;
            }
            
            if (!animating) {
                drawCanvas();
            }
        }
        
        function changeImage() {
            currentImage = document.getElementById('image-select').value;
            updateImageSize();
        }
        
        function handleCanvasClick(e) {
            if (animating) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            // Add point
            points.push({x, y});
            updatePointsList();
            
            // Enable buttons if we have at least 2 points
            if (points.length >= 2) {
                document.getElementById('start-btn').disabled = false;
            }
            
            if (points.length >= 1) {
                document.getElementById('remove-last-btn').disabled = false;
            }
            
            drawCanvas();
        }
        
        function updatePointsList() {
            const pointList = document.getElementById('point-list');
            pointList.innerHTML = '';
            
            points.forEach((point, index) => {
                const pointItem = document.createElement('div');
                pointItem.className = 'point-item';
                pointItem.innerHTML = `<span>Point ${index + 1}: (${Math.round(point.x)}, ${Math.round(point.y)})</span>`;
                pointList.appendChild(pointItem);
            });
        }
        
        function removeLastPoint() {
            if (points.length > 0) {
                points.pop();
                updatePointsList();
                
                if (points.length < 2) {
                    document.getElementById('start-btn').disabled = true;
                }
                
                if (points.length < 1) {
                    document.getElementById('remove-last-btn').disabled = true;
                }
                
                drawCanvas();
            }
        }
        
        function resetCanvas() {
            stopAnimation();
            points = [];
            updatePointsList();
            document.getElementById('start-btn').disabled = true;
            document.getElementById('remove-last-btn').disabled = true;
            drawCanvas();
        }
        
        function startAnimation() {
            if (points.length < 2) return;
            
            animating = true;
            currentPointIndex = 0;
            progress = 0;
            
            document.getElementById('start-btn').disabled = true;
            document.getElementById('stop-btn').disabled = false;
            
            animate();
        }
        
        function stopAnimation() {
            animating = false;
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
            
            document.getElementById('start-btn').disabled = false;
            document.getElementById('stop-btn').disabled = true;
            
            drawCanvas();
        }
        
        function animate() {
            if (!animating) return;
            
            drawCanvas();
            moveImage();
            
            animationId = requestAnimationFrame(animate);
        }
        
        function moveImage() {
            if (currentPointIndex >= points.length - 1) {
                currentPointIndex = 0;
                progress = 0;
            }
            
            const currentPoint = points[currentPointIndex];
            const nextPoint = points[currentPointIndex + 1];
            
            // Calculate position along the path
            const x = currentPoint.x + (nextPoint.x - currentPoint.x) * progress;
            const y = currentPoint.y + (nextPoint.y - currentPoint.y) * progress;
            
            // Calculate angle for rotation
            const angle = Math.atan2(nextPoint.y - currentPoint.y, nextPoint.x - currentPoint.x);
            
            // Draw the image at the current position with rotation
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.drawImage(images[currentImage], -imageWidth/2, -imageHeight/2, imageWidth, imageHeight);
            ctx.restore();
            
            // Update progress
            progress += speed;
            if (progress >= 1) {
                currentPointIndex++;
                progress = 0;
            }
        }
        
        function drawCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Draw path
            if (points.length > 0) {
                ctx.beginPath();
                ctx.moveTo(points[0].x, points[0].y);
                
                for (let i = 1; i < points.length; i++) {
                    ctx.lineTo(points[i].x, points[i].y);
                }
                
                ctx.strokeStyle = '#3498db';
                ctx.lineWidth = 2;
                ctx.stroke();
                
                // Draw points
                points.forEach((point, index) => {
                    ctx.beginPath();
                    ctx.arc(point.x, point.y, 5, 0, Math.PI * 2);
                    ctx.fillStyle = index === 0 ? '#2ecc71' : '#e74c3c';
                    ctx.fill();
                    
                    // Add point number
                    ctx.fillStyle = '#000';
                    ctx.font = '12px Arial';
                    ctx.fillText(index + 1, point.x + 10, point.y - 10);
                });
            }
            
            // If not animating, draw the image at the first point
            if (!animating && points.length > 0) {
                const firstPoint = points[0];
                let angle = 0;
                
                // Calculate angle if there's a second point
                if (points.length > 1) {
                    const secondPoint = points[1];
                    angle = Math.atan2(secondPoint.y - firstPoint.y, secondPoint.x - firstPoint.x);
                }
                
                ctx.save();
                ctx.translate(firstPoint.x, firstPoint.y);
                ctx.rotate(angle);
                ctx.drawImage(images[currentImage], -imageWidth/2, -imageHeight/2, imageWidth, imageHeight);
                ctx.restore();
            }
        }
        
        // Initialize everything when images are loaded
        window.onload = function() {
            let loadedCount = 0;
            for (const key in images) {
                images[key].onload = function() {
                    loadedCount++;
                    if (loadedCount === Object.keys(images).length) {
                        init();
                    }
                };
            }
        };
    </script>
</body>
</html>
