<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>testar pdf</title>
</head>
<body>
    <form name='botao' method='POST' action = "relatorio.php">
    <button class='btn_relatorio'>PDF</button>
    <label>Data inicial</label><br>
        <input type="date" name="datai" value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>"><br>
        <label>Data final</label><br>
        <input type="date" name="dataf" value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>"><br>
        <br>
        <label><input type="radio" name="opcao" value="html" checked> Mostrar HTML</label>
        <label><input type="radio" name="opcao" value="pdf"> Gerar PDF</label>
        <br>
        <input type="submit" value="Gerar">
</form>
</body>
</html>
<?php


?>