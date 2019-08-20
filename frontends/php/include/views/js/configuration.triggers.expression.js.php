<script type="text/javascript">
	function add_var_to_opener_obj(obj, name, value) {
		new_variable = window.opener.document.createElement('input');
		new_variable.type = 'hidden';
		new_variable.name = name;
		new_variable.value = value;
		obj.appendChild(new_variable);
	}

	function insertText(obj, value) {
		<?php if ($this->data['dstfld1'] === 'expression' || $this->data['dstfld1'] === 'recovery_expression'): ?>
			jQuery(obj).val(jQuery(obj).val() + value);
		<?php else: ?>
			jQuery(obj).val(value);
		<?php endif ?>
	}

	jQuery(function($) {
		$('#function').change(function() {
			$('#spec_paramtype').remove();
			$(this).closest('form').submit();
		});

		$.valHooks.input = {
			get: function(elem) {
				return elem.value;
			},
			set: function(elem, value) {
				var tmp = elem.value;
					elem.value = value;

				'description' === elem.id && tmp !== value && $(elem).trigger('change');
			}
		};

		$('#description').change(function() {
			$(this).closest('form').submit();
		});
	});
</script>

<?php
if (!empty($this->data['insert'])) { ?>
	<script type="text/javascript">
		insertText(jQuery('#<?php echo $this->data['dstfld1']; ?>', window.opener.document), <?php echo zbx_jsvalue($this->data['expression']); ?>);
		close_window();
	</script>
<?php
}
if (!empty($this->data['cancel'])) {?>
	<script type="text/javascript">
		close_window();
	</script>
<?php
}
