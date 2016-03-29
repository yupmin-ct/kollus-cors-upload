<?php

require '../vendor/autoload.php';

use Kollus\Component\KollusClient;
use Symfony\Component\Yaml\Parser as YamlParser;

// Get Configuration
$configFilePath = realpath(__DIR__ . '/..') . '/config.yml';
$yamlParser = new YamlParser();
$alertMessage = null;

if (file_exists($configFilePath)) {

    $config = $yamlParser->parse(file_get_contents($configFilePath));

    // Get API Client
    $apiClient = KollusClient::getApiClientBy(
        $config['kollus_domain'],
        $config['version'],
        $config['language_key'],
        $config['service_account']['key'],
        $config['service_account']['api_access_token']
    );

    $action = isset($_GET['action']) ? $_GET['action'] : null;

    if ($action === 'upload_url') {
        $categoryKey = empty($_POST['category_key']) ?
            null : $_POST['category_key'];
        $isEncryptionUpload = empty($_POST['use_encryption']) ?
            null : $_POST['use_encryption'];
        $isAudioUpload = empty($_POST['is_audio_upload']) ?
            null : $_POST['is_audio_upload'];
        $title = empty($_POST['title']) ? null : $_POST['title'];

        header('Content-Type: application/json; charset=utf-8');

        try {
            $response = $apiClient->getUploadURLResponse(
                $categoryKey,
                $isEncryptionUpload,
                $isAudioUpload,
                $title
            );
            echo json_encode(array('result' => $response));
        } catch (ClientException $ce) {
            echo json_encode(array('error' => 1, 'message' => $ce->getMessage));
        }
        exit;
    }

    // Get categories
    $categories = $apiClient->getCategories();
} else {
    $alertMessage = 'Config file is not found.';
}

$serviceAccountKey = isset($config['service_account']['key']) ? $config['service_account']['key'] : null;
$kollusdomain = isset($config['kollus_domain']) ? $config['kollus_domain'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Kollus CORS Upload</title>

<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/css/default.css">
<!--[if lt IE 9]>
<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<nav class="navbar navbar-default  navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed"
              data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Kollus CORS Upload</a>
        </div>
        <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="https://github.com/yupmin-ct/kollus-cors-upload"><i class="fa fa-github fa-lg"></i> Github</a></li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Upload files</h1>
    </div>

    <div id="alert_message">
<?php
if (!empty($alertMessage)) {
echo '<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>'.$alertMessage.'</div>';
}
?>
    </div>

    <form action="index.php?action=upload_url" method="post">
        <fieldset <?php if (!file_exists($configFilePath)) echo 'disabled'; ?>>
<?php
if (file_exists($configFilePath)):
?>
        <div class="form-group">
            <label>Service account key</label>
            <p class="form-control-static"><?php echo $serviceAccountKey; ?></p>
        </div>

        <div class="form-group">
            <label>Kollus domain</label>
            <p class="form-control-static"><?php echo $kollusdomain; ?></p>
        </div>

        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category_key">
<?php
foreach ($categories as $category):
?>
                <option value="<?php echo htmlspecialchars($category->getKey()); ?>"><?php echo htmlspecialchars($category->getName()); ?></option>
<?php
endforeach;
?>
            </select>
        </div>
<?php
endif;
?>
        <div class="form-group">
            <label for="upload-file">Upload file</label>
            <input type="file" class="form-control" id="upload-file" name="upload-file" placeholder="Upload File" multiple>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" name="use_encryption" value="1"> Is encryption
            </label>
        </div>

        <div class="checkbox">
            <label>
                <input type="checkbox" name="is_audio_upload" value="1"> Is audio
            </label>
        </div>

        <div class="form-group">
            <label for="Title">Title (option)</label>
            <input type="text" class="form-control" id="Title" name="title" placeholder="Title">
        </div>

        <button type="submit" class="btn btn-primary" data-action="upload-file"><i class="fa fa-upload"></i> Upload</button>
        </fieldset>
    </form>

    <hr />

    <footer class="footer">
        <p>&copy; 2015 Catenoid, Inc.</p>
    </footer>

</div>
<!--[if lt IE 10]>
<script src="//code.jquery.com/jquery-1.11.3.min.js"></script>t
<![endif]-->
<!--[if (gte IE 10)|!(IE)]><!-->
<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
<!--<![endif]-->
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="assets/js/cors-upload.js"></script>
</body>
</html>
