<div class="row justify-content-center">
	<div class="col-md-6">
		<h1 class="h3 mb-4">タスク編集</h1>

		<?php if ( ! empty($error)): ?>
			<div class="alert alert-danger"><?php echo e($error); ?></div>
		<?php endif; ?>

		<form method="post" action="/tasks/edit/<?php echo $task['id']; ?>">
			<?php echo \Form::csrf(); ?>

			<div class="mb-3">
				<label class="form-label">タスク名</label>
				<input type="text" name="name" class="form-control" value="<?php echo e($name); ?>" required>
			</div>

			<div class="mb-3">
				<label class="form-label">期限</label>
				<input type="date" name="due_date" class="form-control" value="<?php echo e($due_date); ?>" required>
			</div>

			<div class="mb-3">
				<label class="form-label">ステータス</label>
				<select name="task_status_id" class="form-select" required>
					<?php foreach ($statuses as $status): ?>
						<option value="<?php echo $status['id']; ?>" <?php echo ((string) $status_id === (string) $status['id']) ? 'selected' : ''; ?>>
							<?php echo e($status['name']); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="mb-3">
				<label class="form-label">メモ</label>
				<textarea name="memo" class="form-control" rows="4"><?php echo e($memo); ?></textarea>
			</div>

			<div class="d-flex gap-2">
				<a href="/projects/<?php echo $task['project_id']; ?>/tasks" class="btn btn-secondary">キャンセル</a>
				<button type="submit" class="btn btn-primary">保存</button>
			</div>
		</form>
	</div>
</div>
