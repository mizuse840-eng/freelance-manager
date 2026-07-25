<div class="d-flex justify-content-between align-items-center mb-4">
	<h1 class="h3 mb-0">クライアント一覧</h1>
	<a href="/clients/create" class="btn btn-primary">新規登録</a>
</div>

<?php if (empty($clients)): ?>
	<div class="alert alert-info">
		クライアントが登録されていません。
	</div>
<?php else: ?>
	<table class="table table-bordered bg-white">
		<thead class="table-light">
			<tr>
				<th>クライアント名</th>
				<th style="width: 200px;">操作</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($clients as $client): ?>
				<tr>
					<td><?php echo e($client['name']); ?></td>
					<td>
						<a href="/clients/edit/<?php echo $client['id']; ?>" class="btn btn-sm btn-primary">編集</a>
						<a href="/clients/delete/<?php echo $client['id']; ?>" class="btn btn-sm btn-danger">削除</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>