<html>
<head>
<title>Upload csv</title>
</head>
<body>

<?php echo $error;?>

<?php echo form_open_multipart('chart/uploadChart/YEZI', ['date'=>'2017-12-05', 'src'=>'BAIDU']);?>

<input type="file" name="userfile" size="20" />

<br /><br />

<input type="submit" value="upload" />

</form>

</body>
</html>
