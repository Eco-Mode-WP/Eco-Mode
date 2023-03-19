import {
	Chart as ChartJS,
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend,
} from 'chart.js';
import { Bar } from 'react-chartjs-2';
import { faker } from '@faker-js/faker';

ChartJS.register(
	CategoryScale,
	LinearScale,
	BarElement,
	Title,
	Tooltip,
	Legend
);

export const options = {
	responsive: true,
	plugins: {
		legend: {
			position: 'top',
		},
		title: {
			display: true,
			text: 'Chart.js Bar Chart',
		},
	},
};

const labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

export const data = {
	labels,
	datasets: [
		{
			label: 'Initial',
			data: labels.map(() => faker.datatype.number({ min: 0, max: 1000 })),
			backgroundColor: '#f87171',
		},
		{
			label: 'Saved ',
			data: labels.map(() => faker.datatype.number({ min: 0, max: 1000 })),
			backgroundColor: '#34d399',
		},
	],
};

export default function EcoModePerDayChart() {
	return <Bar options={ options } data={ data } />;
}
