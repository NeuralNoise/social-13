<?php
//Инклуды - зацеп
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort');

//prepared_uri
$prepared_uri=array();

echo template_get('header');
echo menu_top();
echo "<br/>";
echo h1("Робот");
echo "<br/>";
?>

<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Робот</title>
<script>
function line(x1, y1, x2, y2)
{
    this.x1 = x1;
    this.y1 = y1;
	this.x2 = x2;
	this.y2 = y2;
	
    this.draw = function(color) // метод рисующий круг
    {
		context.strokeStyle=color;
        context.beginPath();
		context.moveTo(this.x1, this.y1);
		context.lineTo(this.x2, this.y2);
		context.stroke();
    };
}

function rect(x, y, width, height) // класс задающий прямоугольник
{
    this.x = x; // координата х
    this.y = y; // координата у
    this.width = width; // ширина
    this.height = height; // высота
    this.draw = function(color, globalAlpha) // функция рисует прямоугольник согласно заданным параметрам
    {
		context.beginPath();
		context.rect(this.x, this.y, this.width, this.height);
        context.fillStyle = color;
		context.fill();
		context.lineWidth=4;
		context.strokeStyle='#999';
		context.stroke();
    };
}

function menu(x, y, width, height) // класс задающий прямоугольник
{
    this.x = x; // координата х
    this.y = y; // координата у
    this.width = width; // ширина
    this.height = height; // высота
    this.draw = function(color, globalAlpha) // функция рисует прямоугольник согласно заданным параметрам
    {
		context.beginPath();
		context.rect(this.x, this.y, this.width, this.height);
        context.fillStyle = color;
		context.fill();
		context.lineWidth=2;
		context.strokeStyle='#999';
		context.stroke();
    };
}

function apple() // класс задающий прямоугольник
{
	this.getX=function(a){
		return Math.floor((Math.random() * 10))*10;
	}
	this.getY=function(a){
		return Math.floor((Math.random() * 10))*10;
	}
	this.draw = function(color, globalAlpha) // функция рисует прямоугольник согласно заданным параметрам
    {
		if(d%100==0){
			this.x=this.getX();
			this.y=this.getY();
		}
		context.beginPath();
		context.rect(this.x, this.y, 10, 10);
        context.fillStyle = color;
		context.fill();
	}
	this.x=this.getX();
	this.y=this.getY();
}

function udav(x, y, width, height) // класс задающий прямоугольник
{
    this.x = x; // координата х
    this.y = y; // координата у
    this.width = 10; // ширина
    this.height = 10; // высота
    this.draw = function(color, globalAlpha) // функция рисует прямоугольник согласно заданным параметрам
    {
        context.globalAlpha = globalAlpha;
        context.fillStyle = "#999";
        context.fillRect(this.x, this.y, this.width, this.height);
        //context.globalAlpha = globalAlpha;
        //context.fillStyle = "#999";
        context.fillRect(this.x+10, this.y, this.width, this.height);
    };
}

function UdavHead(){
    this.x = x; // координата х
    this.y = y; // координата у
    this.width = 10; // ширина
    this.height = 10; // высота
    this.draw = function(color, globalAlpha) // функция рисует прямоугольник согласно заданным параметрам
    {
        context.globalAlpha = globalAlpha;
        context.fillStyle = "#999";
        context.fillRect(this.x, this.y, this.width, this.height);
        //context.globalAlpha = globalAlpha;
        //context.fillStyle = "#999";
        context.fillRect(this.x+10, this.y, this.width, this.height);
    };	
}

function update() // изменения координат которые нужно произвести
{
	d=d+1;
	if(regime=='forward'){
		udav.x += sw*vX;
	}

	if(regime=='backward'){
		udav.x -= sw*vX;
	}

	if(regime=='bottom'){
		udav.y += sw*vY;
	}

	if(regime=='top'){
		udav.y -= sw*vY;
	}
	
	document.onkeydown = function checkKeycode(event)
	{
		if(event.keyCode==40 && regime=='forward'){
			switch_regime('ForwardToBottom');
		}
		if(event.keyCode==38 && regime=='forward'){
			switch_regime('ForwardToTop');
		}
		if(event.keyCode==40 && regime=='backward'){
			switch_regime('BackwardToBottom');
		}
		if(event.keyCode==38 && regime=='backward'){
			switch_regime('BackwardToTop');
		}
		if(event.keyCode==39 && regime=='bottom'){
			switch_regime('BottomToForward');
		}
		if(event.keyCode==37 && regime=='bottom'){
			switch_regime('BottomToBackward');
		}
		if(event.keyCode==39 && regime=='top'){
			switch_regime('TopToForward');
		}
		if(event.keyCode==37 && regime=='top'){
			switch_regime('TopToBackward');
		}
	}
}
function draw() // рисуем на холсте
{
    game.draw("#333"); // рисуем фон
    udav.draw("#999"); // рисуем шар
	menu.draw("#FFF");
	apple.draw('#F00');
    update(); // обновляем координаты
}
function init() // Инициализация переменных
{
	l=20;
	h=10;
	regime='forward';
	sw=1;
    vX = 10; // скорость шара по оси х
    vY = 10; // скорость шара по оси у
	d=1;
	
    game = new rect(0, 0, 600, 320);
	menu = new menu(470, 2, 129, 80);
    udav = new udav(0, game.height/4, l, h);
	apple=new apple();
    var canvas = document.getElementById("example");
    canvas.width = game.width; // ширина холста
    canvas.height = game.height; // высота холста
    context = canvas.getContext("2d");
    setInterval(draw, 1000 / 4); //отрисовываем 50 раз за секунду
	kolvo_kvadratov=game.width/h*game.height/h;
}
function switch_regime(change){
	if(change=='ForwardToBottom'){
		regime='bottom';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.x=udav.x+l-h;
	}
	if(change=='ForwardToTop'){
		regime='top';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.y-=udav.height-h;
		udav.x+=l-h;
	}
	if(change=='BackwardToBottom'){
		regime='bottom';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
	}
	if(change=='BackwardToTop'){
		regime='top';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.y-=l-h;
	}
	if(change=='BottomToForward'){
		regime='forward';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.y=udav.y+l-h;
	}
	if(change=='BottomToBackward'){
		regime='backward';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.y+=l-h;
	}
	if(change=='TopToForward'){
		regime='forward';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
	}
	if(change=='TopToBackward'){
		regime='backward';
		height=udav.height;
		udav.height=udav.width;
		udav.width=height;
		udav.x-=l-h;
	}
}
</script>
</head>

<body>

        <canvas id="example"></canvas>
        <script>init()</script>

</body>
</html>

<?php
echo template_get('footer');

//Цепляет файл
function pickup(){
	//Получаем список подцепляемых файлов и директорию
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if($folder=='actions'){
		$GLOBALS['actions_for_pickup']=$files;
	}
	
	//Цепляем полученные файлы
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}?>