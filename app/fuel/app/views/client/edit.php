<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">クライアント編集</h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="post" action="/clients/edit/<?php echo $client['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="mb-3">
				<label class="form-label">クライアント名</label>
				<input type="text" name="name" class="form-control" value="<?php echo e($name); ?>" required>
			</div>

			<div class="d-flex gap-2">
				<a href="/clients" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-primary">保存</button>
			</div>
		</form>
	</div>
</div>