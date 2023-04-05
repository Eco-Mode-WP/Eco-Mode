/**
 * External dependencies
 */
/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

function useEcoModeData() {
	const ecoModeData = useMemo(() => window.EcoModeSettings || {}, []);

	return ecoModeData;
}

export default useEcoModeData;
