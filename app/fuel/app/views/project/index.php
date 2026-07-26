<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<a href="/clients" class="text-decoration-none">&larr; クライアント一覧</a>
		<h1 class="h3 mb-0 mt-2"><?php echo e($client['name']); ?>の案件一覧</h1>
	</div>
	<a href="/clients/<?php echo $client['id']; ?>/projects/create" class="btn btn-primary">新規登録</a>
</div>

<?php if (empty($projects)): ?>
	<div class="alert alert-info">
		案件が登録されていません。
	</div>
<?php else: ?>
	<table class="table table-bordered bg-white">
		<thead class="table-light">
			<tr>
				<th>案件名</th>
				<th style="width: 120px;">期限</th>
				<th style="width: 100px;">残り</th>
				<th style="width: 100px;">ステータス</th>
				<th style="width: 160px;">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($projects as $project): ?>
				<?php
					// 残り日数を計算
					$today = new DateTime('today');
					$due   = new DateTime($project['due_date']);
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
					<td><?php echo e($project['name']); ?></td>
					<td><?php echo e($project['due_date']); ?></td>
					<td class="<?php echo $class; ?>"><?php echo e($label); ?></td>
					<td><?php echo e($project['status_name']); ?></td>
					<td>
						<a href="/projects/edit/<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">編集</a>
						<a href="/projects/delete/<?php echo $project['id']; ?>" class="btn btn-sm btn-danger">削除</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>