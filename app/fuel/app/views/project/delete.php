<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">案件削除</h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<div class="alert alert-warning">
			以下の案件を削除します。この操作は取り消せません。
		</div>

		<table class="table table-bordered bg-white mb-4">
			<tr>
				<th style="width: 120px;">案件名</th>
				<td><?php echo e($project['name']); ?></td>
			</tr>
			<tr>
				<th>期限</th>
				<td><?php echo e($project['due_date']); ?></td>
			</tr>
			<tr>
				<th>ステータス</th>
				<td><?php echo e($project['status_name']); ?></td>
			</tr>
			<tr>
				<th>登録されているタスク</th>
				<td><?php echo (int) $task_count; ?>件</td>
			</tr>
		</table>

		<?php if ($task_count > 0): ?>
			<div class="alert alert-danger">
				タスクが登録されているため削除できません。先にタスクをすべて削除してください。
			</div>
		<?php endif; ?>

		<form method="post" action="/projects/delete/<?php echo $project['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="d-flex gap-2">
				<a href="/clients/<?php echo $project['client_id']; ?>/projects" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-danger" <?php echo $task_count > 0 ? 'disabled' : ''; ?>>削除する</button>
			</div>
		</form>
	</div>
</div>