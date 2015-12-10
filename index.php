<?php

require 'vendor/autoload.php';

use Kollus\Component\KollusClient;
use Symfony\Component\Yaml\Parser as YamlParser;

// Get Configuration
$configFilePath = realpath(__DIR__ . '/.') . '/config.yml';
$yamlParser = new YamlParser();
if (!file_exists($configFilePath)) {
    throw new \Exception('Kollus account config is not found');
}
$config = $yamlParser->parse(file_get_contents($configFilePath));

// Get API Client
$apiClient = KollusClient::getApiClientBy(
    $config['kollus_domain'],
    $config['version'],
    $config['language_key'],
    $config['service_account']['key'],
    $config['service_account']['api_access_token']
);

if (isset($_POST['get_upload_url'])) {
    $categoryKey = empty($_POST['category_key']) ?
        null : $_POST['category_key'];
    $isEncryptionUpload = empty($_POST['is_encryption_upload']) ?
        null : $_POST['is_encryption_upload'];
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

$serviceAccountKey = isset($config['service_account']['key']) ? $config['service_account']['key'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Kollus CORS Upload</title>

<link href="/public/assets/css/default.css" rel="stylesheet">
<!--[if lt IE 9]>
<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
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
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Upload file</h1>
        </div>

        <div id="alert_message"></div>

        <form action="index.php" method="post">

            <div class="form-group">
                <label>Service account key</label>
                <p class="form-control-static"><?php echo $serviceAccountKey; ?></p>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category_key">
<?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category->getKey()); ?>"><?php echo htmlspecialchars($category->getName()); ?></option>
<?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="upload-file">Upload file</label>
                <div class="progress" style="display: none;"></div>
                <input type="file" class="form-control" id="upload-file" name="upload-file" placeholder="Upload File">
            </div>

            <div class="checkbox">
                <label>
                    <input type="checkbox" name="is_encryption_upload" value="1"> Is encryption
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

            <button type="submit" class="btn btn-default" data-action="upload-file" autocomplete="off" data-loading-text="<?php echo htmlspecialchars('<i class="fa fa-refresh fa-spin"></i> Uploading ...'); ?>"><i class="fa fa-upload"></i> Upload</button>
        </for>

        <hr />

        <footer class="footer">
            <p>&copy; 2015 Catenoid, Inc.</p>
        </footer>

    </div>


<script src="/public/assets/js/default.js"></script>
<script src="/src/cors-upload.js"></script>

</body>
</html>
