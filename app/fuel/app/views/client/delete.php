<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">クライアント削除</h1>

		<div class="alert alert-warning">
			以下のクライアントを削除します。この操作は取り消せません。
		</div>

		<table class="table table-bordered bg-white mb-4">
			<tr>
				<th style="width: 160px;">クライアント名</th>
				<td><?php echo e($client['name']); ?></td>
			</tr>
		</table>

		<form method="post" action="/clients/delete/<?php echo $client['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="d-flex gap-2">
				<a href="/clients" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-danger">削除する</button>
			</div>
		</form>
	</div>
</div>