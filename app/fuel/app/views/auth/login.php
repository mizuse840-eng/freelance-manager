<div class="row justify-content-center">
	<div class="col-md-5">
		<h1 class="h3 mb-4 text-center">ログイン</h1>

		<?php if (! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="post" action="/login">
			<?php echo \Form::csrf(); ?>

			<div class="mb-3">
				<label class="form-label">メールアドレス</label>
				<input type="email" name="email" class="form-control" required>
			</div>

			<div class="mb-3">
				<label class="form-label">パスワード</label>
				<input type="password" name="password" class="form-control" required>
			</div>

			<button type="submit" class="btn btn-primary w-100">ログイン</button>
		</form>
	</div>
</div>
