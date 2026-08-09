<div class="d-flex justify-content-between align-items-center mb-4">
	<div>
		<a href="/clients/<?php echo $project['client_id']; ?>/projects" class="text-decoration-none">&larr; 案件一覧</a>
		<h1 class="h3 mb-0 mt-2"><?php echo e($project['name']); ?>のタスク一覧</h1>
	</div>
	<a href="/projects/<?php echo $project['id']; ?>/tasks/create" class="btn btn-primary">新規登録</a>
</div>

<div data-bind="visible: message, css: messageClass" class="alert" style="display:none;">
	<span data-bind="text: message"></span>
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
				<th style="width: 140px;">ステータス</th>
				<th>メモ</th>
				<th style="width: 160px;">操作</th>
			</tr>
		</thead>
		<tbody data-bind="foreach: tasks">
			<tr>
				<td data-bind="text: name"></td>
				<td data-bind="text: due_date"></td>
				<td data-bind="text: diff_label, attr: { class: diff_class }"></td>
				<td>
					<select class="form-select form-select-sm" data-bind="value: task_status_id, options: $root.statuses, optionsText: 'name', optionsValue: 'id', event: { change: function() { $root.updateStatus($data); } }"></select>
				</td>
				<td data-bind="html: memo_html"></td>
				<td>
					<a class="btn btn-sm btn-primary" data-bind="attr: { href: '/tasks/edit/' + id }">編集</a>
					<a class="btn btn-sm btn-danger" data-bind="attr: { href: '/tasks/delete/' + id }">削除</a>
				</td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>


<script>
(function () {
	// PHPから初期データを渡す
	var initialTasks = <?php

		// 残り日数の色分け・メモのHTML化はUI設計時のルールのまま維持し、
		// KnockoutへJSONで渡すデータの一部として事前計算しておく
		$tasks_for_js = array();
		foreach ($tasks as $task)
		{
			$deadline = \Deadline::calculate($task['due_date']);

			$tasks_for_js[] = array(
				'id'             => (int) $task['id'],
				'name'           => $task['name'],
				'due_date'       => $task['due_date'],
				'diff_class'     => $deadline['class'],
				'diff_label'     => $deadline['label'],
				'task_status_id' => (int) $task['task_status_id'],
				'status_name'    => $task['status_name'],
				'memo_html'      => nl2br(e($task['memo'] !== null ? $task['memo'] : '')),
			);
		}

		echo json_encode($tasks_for_js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
	?>;
	var initialStatuses = <?php echo json_encode(
		$statuses,
		JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
	); ?>;
	var csrfKey   = <?php echo json_encode(\Config::get('security.csrf_token_key')); ?>;
	var csrfToken = <?php echo json_encode(\Security::fetch_token()); ?>;

	function TaskRow(data) {
		this.id = data.id;
		this.name = data.name;
		this.due_date = data.due_date;
		this.diff_class = data.diff_class;
		this.diff_label = data.diff_label;
		this.memo_html = data.memo_html;
		this.task_status_id = ko.observable(data.task_status_id);
		this.status_name = ko.observable(data.status_name);
	}

	function TaskListViewModel(tasks, statuses) {
		var self = this;

		self.statuses = statuses;

		self.tasks = ko.observableArray(tasks.map(function (t) {
			return new TaskRow(t);
		}));

		self.message = ko.observable('');
		self.messageClass = ko.observable('alert-success');

		self.updateStatus = function (row) {
			var params = new URLSearchParams();
			params.append('id', row.id);
			params.append('task_status_id', row.task_status_id());
			params.append(csrfKey, csrfToken);

			fetch('/tasks/api_status', {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: params.toString()
			})
			.then(function (res) { return res.json(); })
			.then(function (json) {
				// トークンはリクエストごとに再生成されるため、次回送信用に差し替える
				if (json.csrf_token) { csrfToken = json.csrf_token; }

				if (json.success) {
					row.task_status_id(json.task.task_status_id);
					row.status_name(json.task.status_name);
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

	ko.applyBindings(new TaskListViewModel(initialTasks, initialStatuses));
})();
</script>
