<?php
include_once '../config.php';

?>
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/pwa/service-worker.js')
                .then((registration) => {
                    console.log('Service Worker registered with scope:', registration.scope);
                })
                .catch((error) => {
                    console.log('Service Worker registration failed:', error);
                });
        });
    }
</script>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Property Inspection</title>
    <link rel="manifest" href="/manifest.json" />
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/pwa/css/styles.css" />
</head>

<body>

    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0001.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0001.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>

    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0002.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0002.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>

    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0003.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0003.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>
    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0001.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0001.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>

    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0002.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0002.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>

    <div class="gallery">
        <a target="_blank" href="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0003.jpg";?>">
            <img src="<?php echo "https://storage.cloud.google.com/" . gcloud_bucket_inspection_media . "/1/4/" . "img0003.jpg";?>" alt="Office 1 - Bayleys">
        </a>
        <div class="desc">Office 1 - Bayleys: Overall</div>
    </div>

    <script src="/pwa/assets/js/app.js"></script>
    <script>