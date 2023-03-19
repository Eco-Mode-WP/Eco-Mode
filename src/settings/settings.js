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
	const [ timeSpanFilter, setTimeSpanFilter ] = useState('perDay');

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
			<div className="eco-mode__filter">
				<span>Filter:</span>
				<input type="button" value="perDay" onClick={ (event) => handleFilter(event) } />
				<input type="button" value="perMonth" onClick={ (event) => handleFilter(event) } />
			</div>

			<PanelBody initialOpen={true} title={__('WP version check')}>
				<div className="eco-mode__chart-wrapper">
					<div className="eco-mode__chart-panel">
						{
							timeSpanFilter === 'perDay'
								? <EcoModePerDayChart />
								: <EcoModePerMonthChart />
						}
					</div>
					<div className="eco-mode__chart-text">
						<h2>WP version check</h2>
						<p>An 'extremely credible source' has called my office and told me that Lorem Ipsum's birth certificate is a fraud. I’m the best thing that ever happened to placeholder text.</p>
						<ul>
							<li><strong>Prevented CO2 emission</strong>: 123gr</li>
							<li><strong>Saved glacier amount</strong>: 0.003t</li>
						</ul>
					</div>
				</div>
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
