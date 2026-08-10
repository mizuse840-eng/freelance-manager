<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo isset($title) ? e($title) : 'フリーランス案件管理'; ?></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
	<script src="/assets/js/knockout-min.js"></script>
</head>
<body>
	<nav class="navbar navbar-dark bg-dark px-3">
		<span class="navbar-brand">フリーランス案件管理</span>
		<?php if (\Session::get('user_id')): ?>
			<a href="/logout" class="btn btn-outline-light btn-sm">ログアウト</a>
		<?php endif; ?>
	</nav>

	<main class="container py-4">
		<?php echo $content; ?>
	</main>
</body>
</html>
