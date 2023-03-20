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
import EcoModePerDayChart from './components/EcoModePerDayChart';
import EcoModePerMonthChart from './components/EcoModePerMonthChart';
import RequestList from './components/RequestList';

const Settings = () => {
	const [timeSpanFilter, setTimeSpanFilter] = useState('Day');
	const [active, setActive] = useState('filter-one');

	const handleFilter = useCallback(
		(event) => {
			setTimeSpanFilter(event.target.value);
			setActive(event.target.id);
		},
		[timeSpanFilter],
	);

	const ecoModeData = useEcoModeData();
	// Get data.
	// console.log(ecoModeData);
	//ecoModeData?.file_mods?.prevented_requests

	return (
		<>
			<PanelBody initialOpen={true} title={__('Eco Mode usage')}>
				<div className="eco-mode__filter">
					<span>Filter:</span>
					<input
						id={'filter-one'}
						className={'filter-one' === active ? 'active' : ''}
						type="button"
						value="Day"
						onClick={(event) => handleFilter(event)}
					/>
					<input
						id={'filter-two'}
						className={'filter-two' === active ? 'active' : ''}
						type="button"
						value="Month"
						onClick={(event) => handleFilter(event)}
					/>
				</div>
				<div className="eco-mode__chart-wrapper">
					<div className="eco-mode__chart-panel">
						{timeSpanFilter === 'Day' ? (
							<EcoModePerDayChart />
						) : (
							<EcoModePerMonthChart />
						)}
					</div>
					<div className="eco-mode__chart-text">
						<h2>Eco Mode usage</h2>
						<p>
							With the ever-increasing impact of digital
							technology on our planet, it’s more important than
							ever to take steps to reduce our environmental
							impact.
						</p>
						<ul>
							<li>
								<strong>Prevented CO2 emission</strong>: 123gr
							</li>
							<li>
								<strong>Saved glacier amount</strong>: 0.003t
							</li>
						</ul>
					</div>
				</div>
			</PanelBody>
			<PanelBody initialOpen={false} title={__('Request List')}>
				<RequestList requests={ecoModeData.requests} />
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
