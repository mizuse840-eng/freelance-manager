<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">クライアント登録</h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="post" action="/clients/create">
			<?php echo \Form::csrf(); ?>

			<div class="mb-3">
				<label class="form-label">クライアント名</label>
				<input type="text" name="name" class="form-control" value="<?php echo e($name); ?>" required>
			</div>

			<div class="mb-3">
				<label class="form-label">URL（任意）</label>
				<input type="url" name="url" class="form-control" value="<?php echo e($url); ?>" placeholder="https://www.lancers.jp/...">
			</div>

			<div class="d-flex gap-2">
				<a href="/clients" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-primary">保存</button>
			</div>
		</form>
	</div>
</div>