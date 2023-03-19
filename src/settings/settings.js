/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { render } from '@wordpress/element';
/**
 * Internal dependencies
 */
import useEcoModeData from './components/data';

const Settings = () => {
	const ecoModeData = useEcoModeData();
	console.log(ecoModeData);

	/*
	Get data.
	ecoModeData?.file_mods?.prevented_requests
	 */

	return (
		<>
			<PanelBody initialOpen={true} title={__('Eco Mode Settings')}>
				<div className="settings-panel-wrapper">Settings here</div>
			</PanelBody>
		</>
	);
};

const App = () => {
	return <Settings />;
};

export default App;

document.addEventListener('DOMContentLoaded', () => {
	const htmlOutput = document.getElementById('eco-mode-settings');

	if (htmlOutput) {
		render(<App />, htmlOutput);
	}
});
