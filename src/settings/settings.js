/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { render, useState, useCallback } from '@wordpress/element';
/**
 * Internal dependencies
 */
import useEcoModeData from './components/data';
import EcoModePerDayChart from "./components/EcoModePerDayChart";
import EcoModePerMonthChart from "./components/EcoModePerMonthChart";

const Settings = () => {
	const [ timeSpanFilter, setTimeSpanFilter ] = useState('perWeek');

	const handleFilter = useCallback( (event) => {
		setTimeSpanFilter( event.target.value );
	}, [ timeSpanFilter ] );

	console.log( timeSpanFilter );
	const ecoModeData = useEcoModeData();
	// Get data.
	// console.log(ecoModeData);
	//ecoModeData?.file_mods?.prevented_requests

	return (
		<>
			<div className="eco-mode-filter">
				<span>Filter:</span>
				<input type="button" value="perWeek" onClick={ () => handleFilter(event) } />
				<input type="button" value="perMonth" onClick={ () => handleFilter(event) } />
			</div>

			<PanelBody initialOpen={true} title={__('WP version check')}>
				{
					timeSpanFilter === 'perWeek'
						? <EcoModePerDayChart />
						: <EcoModePerMonthChart />
				}
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
