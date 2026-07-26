<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<a href="/clients/<?php echo $project['client_id']; ?>/projects" class="text-decoration-none">&larr; 案件一覧</a>
		<h1 class="h3 mb-0 mt-2"><?php echo e($project['name']); ?>のタスク一覧</h1>
	</div>
	<a href="/projects/<?php echo $project['id']; ?>/tasks/create" class="btn btn-primary">新規登録</a>
</div>

<?php if (empty($tasks)): ?>
	<div class="alert alert-info">
		タスクが登録されていません。
	</div>
<?php else: ?>
	<table class="table table-bordered bg-white">
		<thead class="table-light">
			<tr>
				<th>タスク名</th>
				<th style="width: 120px;">期限</th>
				<th style="width: 100px;">残り</th>
				<th style="width: 100px;">ステータス</th>
				<th>メモ</th>
				<th style="width: 160px;">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($tasks as $task): ?>
				<?php
					// 残り日数を計算
					$today = new DateTime('today');
					$due   = new DateTime($task['due_date']);
					$diff  = (int) $today->diff($due)->format('%r%a');

					// UI設計で決めた色分けルール
					if ($diff < 0)
					{
						$class = 'text-danger';
						$label = (abs($diff)).'日超過';
					}
					elseif ($diff <= 3)
					{
						$class = 'text-danger';
						$label = '残り'.$diff.'日';
					}
					elseif ($diff <= 7)
					{
						$class = 'text-warning';
						$label = '残り'.$diff.'日';
					}
					else
					{
						$class = 'text-success';
						$label = '残り'.$diff.'日';
					}
				?>
				<tr>
					<td><?php echo e($task['name']); ?></td>
					<td><?php echo e($task['due_date']); ?></td>
					<td class="<?php echo $class; ?>"><?php echo e($label); ?></td>
					<td><?php echo e($task['status_name']); ?></td>
					<td><?php echo nl2br(e($task['memo'] !== null ? $task['memo'] : '')); ?></td>
					<td>
						<a href="/tasks/edit/<?php echo $task['id']; ?>" class="btn btn-sm btn-primary">編集</a>
						<a href="/tasks/delete/<?php echo $task['id']; ?>" class="btn btn-sm btn-danger">削除</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>
