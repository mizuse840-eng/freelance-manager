<div class="d-flex justify-content-between align-items-center mb-4">
	<h1 class="h3 mb-0">クライアント一覧</h1>
	<a href="/clients/create" class="btn btn-primary">新規登録</a>
</div>

<div data-bind="visible: message, css: messageClass" class="alert" style="display:none;">
	<span data-bind="text: message"></span>
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
				<th style="width: 220px;">操作</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: clients">
			<tr>
				<td>
					<!-- 表示モード -->
					<span data-bind="visible: ! $data.editing(), text: $data.name, click: function() { $root.startEdit($data); }" style="cursor: pointer;"></span>

					<!-- 編集モード -->
					<div data-bind="visible: $data.editing()" class="d-flex gap-2">
						<input type="text" class="form-control form-control-sm" data-bind="value: $data.editName, valueUpdate: 'input'">
						<button class="btn btn-sm btn-primary" data-bind="click: function() { $root.save($data); }">保存</button>
						<button class="btn btn-sm btn-secondary" data-bind="click: function() { $root.cancel($data); }">取消</button>
					</div>
				</td>
				<td>
					<a class="btn btn-sm btn-primary" data-bind="attr: { href: '/clients/edit/' + $data.id }">編集</a>
					<a class="btn btn-sm btn-danger" data-bind="attr: { href: '/clients/delete/' + $data.id }">削除</a>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>


<script>
(function () {
	// PHPから初期データを渡す
	var initialClients = <?php echo json_encode(
		$clients,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	); ?>;
	var csrfKey   = <?php echo json_encode(\Config::get('security.csrf_token_key')); ?>;
	var csrfToken = <?php echo json_encode(\Security::fetch_token()); ?>;

	function ClientRow(data) {
		this.id       = data.id;
		this.name     = ko.observable(data.name);
		this.editing  = ko.observable(false);
		this.editName = ko.observable(data.name);
	}

	function ClientListViewModel(clients) {
		var self = this;

		self.clients = ko.observableArray(clients.map(function (c) {
			return new ClientRow(c);
		}));

		self.message = ko.observable('');
		self.messageClass = ko.observable('alert-success');

		self.startEdit = function (row) {
			row.editName(row.name());
			row.editing(true);
		};

		self.cancel = function (row) {
			row.editing(false);
		};

		self.save = function (row) {
			var params = new URLSearchParams();
			params.append('id', row.id);
			params.append('name', row.editName());
			params.append(csrfKey, csrfToken);

			fetch('/clients/api_update', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				if (json.success) {
					row.name(json.client.name);
					row.editing(false);
					self.messageClass('alert-success');
					self.message('更新しました。');
				} else {
					self.messageClass('alert-danger');
					self.message(json.message || '更新に失敗しました。');
				}
			})
			.catch(function () {
				self.messageClass('alert-danger');
				self.message('通信エラーが発生しました。');
			});
		};
	}

	ko.applyBindings(new ClientListViewModel(initialClients));
})();
</script>