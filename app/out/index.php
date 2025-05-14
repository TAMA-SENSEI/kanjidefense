<!DOCTYPE HTML>
<html>
    <head>
        <title>Kanji Defence</title>
        <style>
            canvas{
                background-color: transparent;
                position: absolute;
                left: 0px;
                top: 0px;
                height: 100%;
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div>
            <canvas id="background"></canvas>
            <canvas id="Enemy"></canvas>
            <canvas id="Players"></canvas>
        </div>
        <script>
            const bck = document.getElementById("background");
            const enemy = document.getElementById("Enemy");
            const player = document.getElementById("Players");
            const bctx = bck.getContext("2d");
            const words=['a','b','c','d','e','f'];
            const ans=['a','b','c','d','e','f'];
            const ent=[0];
            const env=[0];
            
            setInterval(Main,15);
            
            function Main(){
				clear();
                for(i = 0; i< ent.length; i++){
                    renderen(ent[i],env[i]);
                    ent[i]+=15;
                }

            }
            function clear(){
            	bctx.clearRect(0,0,bck.width, bck.height);
            }
            function renderen(t,v){
                switch (true){
                    case (t<2000):
                        var p = [t,200];
                        bctx.fillText(v,t/10,80);
                        break;
                }
            }
        </script>
    </body>
</html>
