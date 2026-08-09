<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<a href="/clients" class="text-decoration-none">&larr; クライアント一覧</a>
		<h1 class="h3 mb-0 mt-2"><?php echo e($client['name']); ?>の案件一覧</h1>
	</div>
	<a href="/clients/<?php echo $client['id']; ?>/projects/create" class="btn btn-primary">新規登録</a>
</div>

<div data-bind="visible: message, css: messageClass" class="alert" style="display:none;">
	<span data-bind="text: message"></span>
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
				<th style="width: 140px;">ステータス</th>
				<th style="width: 220px;">操作</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: projects">
			<tr>
				<td>
					<span data-bind="text: name"></span>
					<!-- ko if: url -->
					<a class="ms-1 text-decoration-none" data-bind="attr: { href: url }" target="_blank" rel="noopener noreferrer" title="案件ページを開く">🔗</a>
					<!-- /ko -->
				</td>
				<td data-bind="text: due_date"></td>
				<td data-bind="text: diff_label, attr: { class: diff_class }"></td>
				<td>
					<select class="form-select form-select-sm" data-bind="value: project_status_id, options: $root.statuses, optionsText: 'name', optionsValue: 'id', event: { change: function() { $root.updateStatus($data); } }"></select>
				</td>
				<td>
					<a class="btn btn-sm btn-primary" data-bind="attr: { href: '/projects/edit/' + id }">編集</a>
					<a class="btn btn-sm btn-success" data-bind="attr: { href: '/projects/' + id + '/tasks' }">詳細</a>
					<a class="btn btn-sm btn-danger" data-bind="attr: { href: '/projects/delete/' + id }">削除</a>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>


<script>
(function () {
	// PHPから初期データを渡す
	var initialProjects = <?php

		// 残り日数の色分けはUI設計時のルールのまま維持し、
		// KnockoutへJSONで渡すデータの一部として事前計算しておく
		$projects_for_js = array();
		foreach ($projects as $project)
		{
			$deadline = \Deadline::calculate($project['due_date']);

			$projects_for_js[] = array(
				'id'                => (int) $project['id'],
				'name'              => $project['name'],
				'url'               => $project['url'],
				'due_date'          => $project['due_date'],
				'diff_class'        => $deadline['class'],
				'diff_label'        => $deadline['label'],
				'project_status_id' => (int) $project['project_status_id'],
				'status_name'       => $project['status_name'],
			);
		}

		echo json_encode($projects_for_js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
	?>;
	var initialStatuses = <?php echo json_encode(
		$statuses,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	); ?>;
	var csrfKey   = <?php echo json_encode(\Config::get('security.csrf_token_key')); ?>;
	var csrfToken = <?php echo json_encode(\Security::fetch_token()); ?>;

	function ProjectRow(data) {
		this.id = data.id;
		this.name = data.name;
		this.url = data.url || '';
		this.due_date = data.due_date;
		this.diff_class = data.diff_class;
		this.diff_label = data.diff_label;
		this.project_status_id = ko.observable(data.project_status_id);
		this.status_name = ko.observable(data.status_name);
	}

	function ProjectListViewModel(projects, statuses) {
		var self = this;

		self.statuses = statuses;

		self.projects = ko.observableArray(projects.map(function (p) {
			return new ProjectRow(p);
		}));

		self.message = ko.observable('');
		self.messageClass = ko.observable('alert-success');

		self.updateStatus = function (row) {
			var params = new URLSearchParams();
			params.append('id', row.id);
			params.append('project_status_id', row.project_status_id());
			params.append(csrfKey, csrfToken);

			fetch('/projects/api_status', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				// トークンはリクエストごとに再生成されるため、次回送信用に差し替える
				if (json.csrf_token) { csrfToken = json.csrf_token; }

				if (json.success) {
					row.project_status_id(json.project.project_status_id);
					row.status_name(json.project.status_name);
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

	ko.applyBindings(new ProjectListViewModel(initialProjects, initialStatuses));
})();
</script>
