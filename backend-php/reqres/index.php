<?php
// Carpeta que queremos escanear (la actual)
$directorio = ".";
$archivos = scandir($directorio);

echo "<html><head><title>Índice de Archivos</title>";
echo "<style>body{font-family:sans-serif; padding:20px;} li{margin:5px 0;} a{text-decoration:none; color:#007bff;} a:hover{text-decoration:underline;}</style>";
echo "</head><body>";
echo "<h1>📁 Contenido de /backend-php</h1>";
echo "<ul>";

foreach ($archivos as $archivo) {
    // Ocultamos los puntos de navegación y el propio index.php si prefieres
    if ($archivo != "." && $archivo != "..") {
        // Verificamos si es una carpeta o un archivo para poner un icono
        $icono = is_dir($archivo) ? "📁" : "📄";
        echo "<li>$icono <a href='$archivo'>$archivo</a></li>";
    }
}

echo "</ul></body></html>";
?>