<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">クライアント削除</h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<div class="alert alert-warning">
			以下のクライアントを削除します。この操作は取り消せません。
		</div>

		<table class="table table-bordered bg-white mb-4">
			<tr>
				<th style="width: 160px;">クライアント名</th>
				<td><?php echo e($client['name']); ?></td>
			</tr>
			<tr>
				<th>登録されている案件</th>
				<td><?php echo (int) $project_count; ?>件</td>
			</tr>
		</table>

		<?php if ($project_count > 0): ?>
			<div class="alert alert-danger">
				案件が登録されているため削除できません。先に案件をすべて削除してください。
			</div>
		<?php endif; ?>

		<form method="post" action="/clients/delete/<?php echo $client['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="d-flex gap-2">
				<a href="/clients" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-danger" <?php echo $project_count > 0 ? 'disabled' : ''; ?>>削除する</button>
			</div>
		</form>
	</div>
</div>
