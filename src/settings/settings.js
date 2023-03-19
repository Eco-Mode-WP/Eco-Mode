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
import EcoModePerDayChart from "./components/EcoModePerDayChart";

const Settings = () => {
	const ecoModeData = useEcoModeData();
	/*
	Get data.
	console.log(ecoModeData);
	ecoModeData?.file_mods?.prevented_requests
	 */

	return (
		<>
			<PanelBody initialOpen={true} title={__('Eco Mode Settings')}>
				<div className="settings-panel-wrapper">Settings here</div>
				<EcoModePerDayChart />
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
