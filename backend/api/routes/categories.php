<?php

// Carregar classes necessárias
require_once '../src/Domain/Category/Category.php';
require_once '../src/Infrastructure/Database/CategoryRepositoryImpl.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if ($method === 'GET' && $path === 'categories' || $path === 'categories/') {
    $categoryRepository = new CategoryRepositoryImpl($db);
    $categories = $categoryRepository->all();

    $response = [
        'success' => true,
        'categories' => array_map(function($cat) {
            return [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'description' => $cat->getDescription(),
                'icon' => $cat->getIcon(),
                'allowed_file_types' => $cat->getAllowedFileTypes(),
                'base_price_kz' => $cat->getBasePriceKz(),
                'requires_review' => $cat->requiresReview(),
                'upload_count' => $cat->getUploadCount(),
                'download_count' => $cat->getDownloadCount(),
                'revenue_kz' => $cat->getRevenueKz()
            ];
        }, $categories),
        'count' => count($categories)
    ];

    echo json_encode($response);

} elseif ($method === 'GET' && strpos($path, 'categories/') === 0) {
    $categoryId = str_replace('categories/', '', $path);
    $categoryRepository = new CategoryRepositoryImpl($db);
    $category = $categoryRepository->findById($categoryId);

    if ($category) {
        echo json_encode([
            'success' => true,
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'description' => $category->getDescription(),
                'icon' => $category->getIcon(),
                'allowed_file_types' => $category->getAllowedFileTypes(),
                'base_price_kz' => $category->getBasePriceKz(),
                'requires_review' => $category->requiresReview()
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Categoria não encontrada']);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
}
?>
