/**
 * External dependencies
 */
/**
 * WordPress dependencies
 */
import { useState, useEffect, useMemo } from '@wordpress/element';

function useEcoModeData() {
	const [ecoModeData, setEcoModeData] = useState({});
	const data = useMemo(
		() => window.EcoModeSettings || {},
		[window.EcoModeSettings],
	);

	useEffect(() => {
		if (data) {
			setEcoModeData(data);
		}
	}, [data]);
	return ecoModeData;
}

export default useEcoModeData;
