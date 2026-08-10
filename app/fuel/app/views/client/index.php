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
				<th style="width: 260px;">操作</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: clients">
			<tr>
				<td>
					<!-- 表示モード（KnockoutのvisibleはインラインスタイルでON/OFFするため、
					     Bootstrapのd-flex等(!important)を持つ要素に直接バインドすると
					     非表示にならない。visible対象はクラス無しの要素にし、
					     レイアウト用のd-flexは内側の要素に付ける） -->
					<div data-bind="visible: ! editing()">
						<span data-bind="text: name"></span>
					</div>

					<!-- 編集モード -->
					<div data-bind="visible: editing">
						<input type="text" class="form-control form-control-sm" style="max-width: 420px;" data-bind="value: editName, valueUpdate: 'input'">
					</div>
				</td>
				<td>
					<!-- 表示モード -->
					<div data-bind="visible: ! editing()">
						<button class="btn btn-sm btn-primary" data-bind="click: function() { $root.startEdit($data); }">編集</button>
						<a class="btn btn-sm btn-success" data-bind="attr: { href: '/clients/' + $data.id + '/projects' }">詳細</a>
						<a class="btn btn-sm btn-danger" data-bind="attr: { href: '/clients/delete/' + $data.id }">削除</a>
					</div>

					<!-- 編集モード -->
					<div data-bind="visible: editing">
						<button class="btn btn-sm btn-primary" data-bind="click: function() { $root.save($data); }">保存</button>
						<button class="btn btn-sm btn-secondary" data-bind="click: function() { $root.cancel($data); }">取消</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>


<script>
(function () {
	// PHPから初期データを渡す
	const initialClients = <?php echo json_encode(
		$clients,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	); ?>;
	const csrfKey = <?php echo json_encode(\Config::get('security.csrf_token_key')); ?>;
	let csrfToken = <?php echo json_encode(\Security::fetch_token()); ?>;

	function ClientRow(data) {
		this.id       = data.id;
		this.name     = ko.observable(data.name);
		this.editing  = ko.observable(false);
		this.editName = ko.observable(data.name);
	}

	function ClientListViewModel(clients) {
		const self = this;

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
			row.editName(row.name());
			row.editing(false);
		};

		self.save = function (row) {
			const params = new URLSearchParams();
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
				// トークンはリクエストごとに再生成されるため、次回送信用に差し替える
				if (json.csrf_token) { csrfToken = json.csrf_token; }

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
