/**
 * External dependencies
 */
/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';

function useEcoModeData() {
	const [ecoModeData, setEcoModeData] = useState({});
	const data = window.EcoModeSettings || {};

	useEffect(() => {
		if (data) {
			setEcoModeData(data);
		}
	}, [data]);
	return ecoModeData;
}

export default useEcoModeData;
