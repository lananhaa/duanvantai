<?php
$pageTitle = $pageTitle ?? 'LogisTech';
$pageSubtitle = $pageSubtitle ?? '';
$searchPage = $searchPage ?? ($_GET['page'] ?? 'trangchu');
$searchPlaceholder = $searchPlaceholder ?? 'Tìm kiếm...';
$keyword = $keyword ?? ($_GET['keyword'] ?? '');
$hideSearch = $hideSearch ?? false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - LogisTech</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="dashboard-body">
<?php include 'views/menu.php'; ?>
<main class="main-content">
    <header class="topbar">
        <div class="search-bar">
            <?php if ($hideSearch): ?>
                <span class="nav-text"><?php echo htmlspecialchars($pageTitle); ?></span>
            <?php else: ?>
            <form action="index.php" method="GET" class="order-search-form">
                <input type="hidden" name="page" value="<?php echo htmlspecialchars($searchPage); ?>">
                <i class="fas fa-search search-icon"></i>
                <input type="text" name="keyword" placeholder="<?php echo htmlspecialchars($searchPlaceholder); ?>" value="<?php echo htmlspecialchars($keyword); ?>">
            </form>
            <?php endif; ?>
        </div>
        <div class="topbar-right">
            <a class="user-dropdown" href="index.php?page=profile" style="text-decoration:none;color:inherit;">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=4361ee&color=fff" class="topbar-avatar" alt="User">
                <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>!</span>
            </a>
        </div>
    </header>
    <div class="content-area">
        <div class="page-header">
            <div>
                <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
                <?php if ($pageSubtitle !== ''): ?><p class="page-subtitle"><?php echo htmlspecialchars($pageSubtitle); ?></p><?php endif; ?>
            </div>
            <?php if (!empty($headerActions)) echo $headerActions; ?>
        </div>
        <?php if (!empty($flash)): ?>
            <div class="alert-message <?php echo !empty($flash['success']) ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($flash['message'] ?? ''); ?></div>
        <?php endif; ?>
