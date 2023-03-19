export default function RequestList( props ) {
	if ( ! props.requests ) {
		return null;
	}
	const rows = props.requests.map( ( request ) => {
		const aggregate = request.history.reduce( ( summary, event ) => {
			summary.total ++;
			summary.totalRuntime += event.runtime_in_s;
			summary.totalResponseSize += event.response_size;
			summary.totalRequestSize += event.request_size;
			summary.averageRuntime = summary.totalRuntime / summary.total;
			summary.averageResponseSize =
				summary.totalResponseSize / summary.total;
			summary.averageRequestSize =
				summary.totalRequestSize / summary.total;
			return summary;
		}, {
			total: 0,
			totalRuntime: 0,
			totalResponseSize: 0,
			totalRequestSize: 0,
			averageRuntime: 0,
			averageResponseSize: 0,
			averageRequestSize: 0,
		} );

		return (
			<tr>
				<td>
					{ request.post_title
						.replace( 'GET_', 'GET ' )
						.replace( 'POST_', 'POST ' ) }
				</td>
				<td>{ aggregate.total }</td>
				<td>{ parseFloat( aggregate.averageRuntime ).toFixed( 2 ) } s</td>
				<td>{ parseFloat( aggregate.totalRequestSize / 1000 ).toFixed( 2 ) } kB</td>
				<td>{ parseFloat( aggregate.totalResponseSize / 1000 ).toFixed( 2 ) } kB</td>
			</tr>
		);
	} );

	return <table>
		<thead>
			<th>URL</th>
			<th>Total</th>
			<th>Average runtime</th>
			<th>Total request size</th>
			<th>Total response size</th>
		</thead>
		<tbody>
			{ rows }
		</tbody>
	</table>
}
