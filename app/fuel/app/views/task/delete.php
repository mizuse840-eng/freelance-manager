<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">タスク削除</h1>

		<div class="alert alert-warning">
			以下のタスクを削除します。この操作は取り消せません。
		</div>

		<table class="table table-bordered bg-white mb-4">
			<tr>
				<th style="width: 120px;">タスク名</th>
				<td><?php echo e($task['name']); ?></td>
			</tr>
			<tr>
				<th>期限</th>
				<td><?php echo e($task['due_date']); ?></td>
			</tr>
			<tr>
				<th>ステータス</th>
				<td><?php echo e($task['status_name']); ?></td>
			</tr>
			<tr>
				<th>メモ</th>
				<td><?php echo nl2br(e($task['memo'] !== null ? $task['memo'] : '')); ?></td>
			</tr>
		</table>

		<form method="post" action="/tasks/delete/<?php echo $task['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="d-flex gap-2">
				<a href="/projects/<?php echo $task['project_id']; ?>/tasks" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-danger">削除する</button>
			</div>
		</form>
	</div>
</div>
