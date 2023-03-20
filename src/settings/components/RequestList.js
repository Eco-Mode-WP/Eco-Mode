/**
 * WordPress dependencies
 */
import { RangeControl } from '@wordpress/components';
import { more } from '@wordpress/icons';
import { useState } from '@wordpress/element';

const RequestControl = () => {
	const [number, setNumber] = useState(3);
	return (
		<RangeControl
			help="Adjust rate of requests"
			allowReset
			resetFallbackValue={3}
			step={1}
			withInputField={false}
			icon={more}
			separatorType="none"
			trackColor="green"
			isShiftStepEnabled
			marks={[
				{
					value: 0,
					label: 'Disable',
				},
				{
					value: 1,
					label: 'Low',
				},
				{
					value: 2,
					label: 'High',
				},
			]}
			railColor="red"
			renderTooltipContent={() => 'Adjust rate'}
			value={number}
			onChange={(value) => setNumber(value)}
			min={0}
			max={3}
		/>
	);
};

export default function RequestList(props) {
	if (!props.requests) {
		return null;
	}

	const rows = props.requests
		.filter((request) => !request.post_title.includes('wp-cron'))
		.map((request) => {
			const aggregate = request.history.reduce(
				(summary, event) => {
					summary.total++;
					summary.totalRuntime += event.runtime_in_s;
					summary.totalResponseSize += event.response_size;
					summary.totalRequestSize += event.request_size;
					summary.averageRuntime =
						summary.totalRuntime / summary.total;
					summary.averageResponseSize =
						summary.totalResponseSize / summary.total;
					summary.averageRequestSize =
						summary.totalRequestSize / summary.total;
					return summary;
				},
				{
					total: 0,
					totalRuntime: 0,
					totalResponseSize: 0,
					totalRequestSize: 0,
					averageRuntime: 0,
					averageResponseSize: 0,
					averageRequestSize: 0,
				},
			);

			return (
				<div className="eco-mode__table-body-row">
					<div className="eco-mode__table-column url">
						{request.post_title
							.replace('GET_', 'GET - ')
							.replace('POST_', 'POST - ')}
					</div>
					<div className="eco-mode__table-column total">
						{aggregate.total}
					</div>
					<div className="eco-mode__table-column avg-runtime">
						{parseFloat(aggregate.averageRuntime).toFixed(2)} s
					</div>
					<div className="eco-mode__table-column req-size">
						{parseFloat(aggregate.totalRequestSize / 1000).toFixed(
							2,
						)}{' '}
						kB
					</div>
					<div className="eco-mode__table-column res-size">
						{parseFloat(aggregate.totalResponseSize / 1000).toFixed(
							2,
						)}{' '}
						kB
					</div>
					<div className="eco-mode__table-column res-adjust">
						<div>
							<RequestControl />
						</div>
					</div>
				</div>
			);
		});

	return (
		<div className="eco-mode__table">
			<div className="eco-mode__table-header">
				<div className="eco-mode__table-column url">URL</div>
				<div className="eco-mode__table-column total">Total</div>
				<div className="eco-mode__table-column avg-runtime">
					Average runtime
				</div>
				<div className="eco-mode__table-column req-size">
					Total request size
				</div>
				<div className="eco-mode__table-column res-size">
					Total response size
				</div>
				<div className="eco-mode__table-column res-adjust">
					Adjust Frequency
				</div>
			</div>
			<div className="eco-mode__table-body">{rows}</div>
		</div>
	);
}
