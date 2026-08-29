<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BR_Deactivator {

	/**
	 * Intentionally does nothing destructive.
	 * Data, table, and options all remain in place so reactivating
	 * the plugin restores full functionality immediately.
	 */
	public static function deactivate() {
		// No-op by design. Kept as an explicit hook target for future
		// non-destructive cleanup (e.g. clearing transient locks) only.
	}
}
