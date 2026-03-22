<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'base_academica_petrochamp');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

require_once '../../backend/src/Infrastructure/Database/PDOConnection.php';
require_once '../../backend/src/Domain/Id.php';
require_once '../../backend/src/Domain/User/UserId.php';
require_once '../../backend/src/Domain/Document/Document.php';
require_once '../../backend/src/Domain/Category/Category.php';
require_once '../../backend/src/Infrastructure/Database/DocumentRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/UserRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/CategoryRepositoryImpl.php';
require_once '../../backend/src/Infrastructure/Database/ReviewRepositoryImpl.php';
require_once '../../backend/src/Application/DocumentService.php';
require_once '../../backend/src/Application/ReviewService.php';
require_once '../../backend/src/Application/UrlEncryptService.php';
require_once '../../backend/src/Infrastructure/Security/JWTService.php';
require_once '../../backend/src/Application/PaymentMethodService.php'; 
require_once '../../backend/src/Domain/PaymentMethod/PaymentMethod.php'; 
require_once '../../backend/src/Infrastructure/Database/PaymentMethodRepositoryImpl.php'; 


$db = PDOConnection::getInstance()->getConnection();

$reviewService = new ReviewService($db);
$documentRepository = new DocumentRepositoryImpl($db);
$userRepository = new UserRepositoryImpl($db);
$categoryRepository = new CategoryRepositoryImpl($db);
$documentService = new DocumentService($documentRepository, $categoryRepository, $uploadDir);
$repositoryPM = new PaymentMethodRepositoryImpl($db); 
$servicePM = new PaymentMethodService($repositoryPM); 

$role = $_SESSION['type_auth'];
$id_auth = $_SESSION['id_auth'];

function renderColorStatus($status) {
    if ($status == "PENDENTE") {
        return '<span class="badge gray">'.$status.'</span>';
    } elseif ($status == "REJEITADO") {
        return '<span class="badge red">'.$status.'</span>';
    } elseif ($status == "APROVADO") {
        return '<span class="badge blue">'.$status.'</span>';
    } elseif ($status == "PUBLICADO") {
        return '<span class="badge green">'.$status.'</span>'; 
    } elseif ($status == "PRGRAMADO") {
        return '<span class="badge crimson">'.$status.'</span>';
    } elseif ($status == "AGUARDANDO PAGAMENTO") {
        return '<span class="badge orange">'.$status.'</span>';
    } elseif ($status == "PAGO") {
        return '<span class="badge gold">'.$status.'</span>';
    }else {
        return '<span class="badge gray">'.$status.'</span>';
    }
}

function calcularMediaAvaliacoes(array $reviews): array
{
    if (count($reviews) === 0) {
        return [
            "media" => 0.0,
            "count" => 0,
            "stars" => '
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
                <i class="fa fa-star"></i>
            '
        ];
    }
    
    $count = 0;
    $sum = 0;

    $ratings = array_column($reviews, "rating");
    for ($i=0; $i < count($reviews); $i++) { 
        $count += 1;
        $sum += (int)$reviews[$i]["rating"];
    }

    $media  = $sum / $count;
    $mediaFormated = round($media, 1);

    $starsComplete = floor($media);
    $starMedia = ($media - $starsComplete) >= 0.5 ? 1 : 0;
    $starsEmpty = 5 - ($starsComplete + $starMedia);
    $stars = '';

    for ($i=0; $i < $starsComplete; $i++) { 
        $stars .= '<i class="fa fa-star" style="color: orange;"></i>'; 
    }

    if ($starMedia) {
        $stars .= '<i class="fa fa-star-half" style="color: orange; margin-right: 5px;"></i>'; 
    }

    for ($i=0; $i < $starsEmpty; $i++) { 
        $stars .= '<i class="fa fa-star"></i>'; 
    }

    return [
        "media" => $mediaFormated,
        "count" => $count,
        "stars" => $stars
    ];  
}

function arrayForString($authors_list) {
    $authors = array_map(function($item) {
        return trim($item, '[]"');
    }, $authors_list);

    $result = implode(", ", $authors);
    return $result;
}

function renderStars($rat): string {
    $rating = (int)$rat;
    $maxStars = 5;
    $stars = '';

    for ($i=1; $i <= $maxStars; $i++) { 
        if ($i < $rating) {
            $stars .= '<i class="fa fa-star" style="color: orange;"></i>'; 
        } else {
            $stars .= '<i class="fa fa-star"></i>'; 
        }
    }

    return $stars;
}

function getInitials(string $name): string {
    $name = trim($name);

    if ($name === '') {
        return '';
    }

    $parts = explode(' ', $name);

    $initials = '';

    foreach ($parts as $part) {
        if ($part !== '') {
            $initials .= strtoupper($part[0]);
        }
    }

    return $initials;
}
?>