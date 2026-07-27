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
				<th style="width: 220px;">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($projects as $project): ?>
				<?php $deadline = \Deadline::calculate($project['due_date']); ?>
					
				<tr>
					<td><?php echo e($project['name']); ?></td>
					<td><?php echo e($project['due_date']); ?></td>
					<td class="<?php echo $deadline['class']; ?>"><?php echo e($deadline['label']); ?></td>
					<td><?php echo e($project['status_name']); ?></td>
					<td>
						<a href="/projects/<?php echo $project['id']; ?>/tasks" class="btn btn-sm btn-success">詳細</a>
						<a href="/projects/edit/<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">編集</a>
						<a href="/projects/delete/<?php echo $project['id']; ?>" class="btn btn-sm btn-danger">削除</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>