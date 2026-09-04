<div>
    <div class="flex justify-center">
    <div class="w-full lg:w-10/12">
            <div class="mb-6">
                <flux:breadcrumbs>
                    <flux:breadcrumbs.item href="route('admin.dashboard')" separator="slash" class="text-xs">{{__('Admin')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item href="#" separator="slash" class="text-xs">{{__('Accounts')}}</flux:breadcrumbs.item>
                    <flux:breadcrumbs.item separator="slash" class="text-xs">{{__('Dashboard')}}</flux:breadcrumbs.item>
                </flux:breadcrumbs>
            </div>

            <div class="mb-9">
                <h1 class="font-bold text-2xl">{{ __('Accounts Dashboard') }}</h1>
                <flux:subheading size="md" class="mb-6">{{ __('Overview of your customer accounts analysis') }}</flux:subheading>
            </div>

            <div class="mb-12">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-700 mb-2">{{ __('Total Registered Users') }}</div>
                                <div class="text-2xl font-extrabold">{{ $totalUsers }}</div>
                            </div>
                            <flux:icon.user-group class="size-12 text-blue-500"/>
                        </div>
                    </div>

                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-700 mb-2">{{ __('Current Online Users') }}</div>
                                <div class="text-2xl font-extrabold">{{ $onlineUsers }}</div>
                            </div>
                            <flux:icon.user-round-search class="size-12 text-amber-500"/>
                        </div>
                    </div>

                    <div class="bold-label md:border border-(--default-border-color) rounded-xl md:p-7 dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-700 mb-2">{{ __('Visitors Today (Registered)') }}</div>
                                <div class="text-2xl font-extrabold">{{ $visitorsToday }}</div>
                            </div>
                            <flux:icon.user-check class="size-12 text-green-500"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-(--default-border-color) dark:border-white/8 dark:bg-(--default-element-light-bg-color) mb-12">
                <div class="p-9">
                    <h3 class="font-semibold mb-6">{{ __('Registered User Countries') }}</h3>
                    <div>
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="w-full lg:w-7/12">
                                <div class="mt-3">
                                    @if ($google_maps)
                                        <div id="countries-analytics-chart" class="h-83"></div>
                                    @else
                                        <div class="text-center">
                                            <p class="text-xs mt-6">{{ __('Google Maps is Disabled') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="w-full lg:w-5/12">
                                <div class="mt-3">
                                    <h6 class="font-semibold text-sm mb-3">{{ __('Top 20 Countries') }}</h6>
                                    @php $chunks = array_chunk($user_countries['top_countries']->toArray(), 15, true); @endphp
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                                        @foreach ($chunks as $chunk)
                                            <ul>
                                                @foreach ($chunk as $code => $count)
                                                    @php $country = config('countries.' . $code); @endphp
                                                    <li class="text-sm mb-2">
                                                        <span>{{ $country['flagEmoji'] ?? '' }}</span>
                                                        <span class="text-gray-500">{{ $country['name'] ?? $code }}</span>: <span>{{ $count }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-(--default-border-color) dark:border-white/8 dark:bg-(--default-element-light-bg-color) mb-12">
                <div class="p-9">
                    <h3 class="font-semibold mb-6">{{ __('New Registered Users') }} <span class="text-gray-500">({{ now()->translatedFormat('F') }})</span></h3>
                     <div class="mb-6">
                        <p class="mb-1 text-sm text-gray-500">{{ __('Total Current Month Users') }}</p>
                        <h3 class="text-md font-semibold">{{ number_format($totalCurrentUsers) }}</h3>
                    </div>
                    <div>
                        <canvas id="chart-total-month" class="h-90"></canvas>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-(--default-border-color) dark:border-white/8 dark:bg-(--default-element-light-bg-color)">
                <div class="p-9">
                    <h3 class="font-semibold mb-6">{{ __('Total Registered Users') }} <span class="text-gray-500">({{ now()->translatedFormat('Y') }})</span></h3>
                    <div class="mb-6">
                        <p class="mb-1 text-sm text-gray-500">{{ __('Total Users') }}</p>
                        <h3 class="text-md font-semibold">{{ number_format($totalUsers) }}</h3>
                    </div>
                    <div>
                        <canvas id="chart-total-year" class="h-90"></canvas>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

</div>

@push('scripts')
	<!-- Chart JS -->
	<script src="{{URL::asset('plugins/chart/chart.min.js')}}"></script>	
	<script src="{{URL::asset('plugins/googlemaps/loader.js')}}"></script>	
	<script type="text/javascript">
		document.addEventListener('DOMContentLoaded', function() {
	
			'use strict';

            let freeData = JSON.parse(`<?php echo $chart_data['current_year_registrations']; ?>`);
			let freeDataset = Object.values(freeData);
			let delayed1;

			let ctx = document.getElementById('chart-total-year');
			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: ['{{ __('Jan') }}', '{{ __('Feb') }}', '{{ __('Mar') }}', '{{ __('Apr') }}', '{{ __('May') }}', '{{ __('Jun') }}', '{{ __('Jul') }}', '{{ __('Aug') }}', '{{ __('Sep') }}', '{{ __('Oct') }}', '{{ __('Nov') }}', '{{ __('Dec') }}'],
					datasets: [{
						label: '{{ __('Total Users') }}',
						data: freeDataset,
						backgroundColor: '#1e1e2d',
						borderWidth: 1,
						borderRadius: 20,
						barPercentage: 0.5,
						fill: true
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false,
						labels: {
							display: false
						}
					},
					responsive: true,
					animation: {
						onComplete: () => {
							delayed1 = true;
						},
						delay: (context) => {
							let delay = 0;
							if (context.type === 'data' && context.mode === 'default' && !delayed1) {
								delay = context.dataIndex * 50 + context.datasetIndex * 5;
							}
							return delay;
						},
					},
					scales: {
						y: {
							stacked: true,
							ticks: {
								beginAtZero: true,
								font: {
									size: 10
								},
								stepSize: 40,
							},
							grid: {
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						},
						x: {
							stacked: true,
							ticks: {
								font: {
									size: 10
								}
							},
							grid: {
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						}
					},
					plugins: {
						tooltip: {
							cornerRadius: 10,
							xPadding: 10,
							yPadding: 10,
							backgroundColor: '#000000',
							titleColor: '#FF9D00',
							yAlign: 'bottom',
							xAlign: 'center',
						},
						legend: {
							position: 'bottom',
							labels: {
								boxWidth: 10,
								font: {
									size: 10
								}
							}
						}
					}
				}
			});


			let paymentData2 = JSON.parse(`<?php echo $chart_data['current_month_registrations']; ?>`);
			let paymentDataset = Object.values(paymentData2);
			let delayed;

			let ctx2 = document.getElementById('chart-total-month').getContext('2d');
			new Chart(ctx2, {
				type: 'bar',
				data: {
					labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'],
					datasets: [{
						label: '{{ __('New Users') }}',
						data: paymentDataset,
						backgroundColor: '#007bff',
						borderWidth: 1,
						borderRadius: 20,
						barPercentage: 0.5,
						fill: true
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false,
						labels: {
							display: false
						}
					},
					responsive: true,
					animation: {
						onComplete: () => {
							delayed = true;
						},
						delay: (context) => {
							let delay = 0;
							if (context.type === 'data' && context.mode === 'default' && !delayed) {
								delay = context.dataIndex * 50 + context.datasetIndex * 5;
							}
							return delay;
						},
					},
					scales: {
						y: {
							beginAtZero: true,
							ticks: {
								stepSize: 20,
								font: {
									size: 10
								}
							},
							grid: {
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						},
						x: {
							ticks: {
								font: {
									size: 10
								}
							},
							grid: {								
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						}
					},
					plugins: {
						tooltip: {
							cornerRadius: 10,
							xPadding: 10,
							yPadding: 10,
							backgroundColor: '#000000',
							titleColor: '#FF9D00',
							yAlign: 'bottom',
							xAlign: 'center',
						},
						legend: {
							position: 'bottom',
							labels: {
								boxWidth: 10,
								font: {
									size: 10
								}
							}
						}
					}
				}
			});

			

			let paymentData = @json(json_decode($chart_data['user_countries']));
			let sessionData = [];
			for (const [key, value] of Object.entries(paymentData)) {
				sessionData.push([`${key}`, `${value}`]);
			}

			google.charts.load('current', {
				'packages':['geochart'],
				// Note: you will need to get a mapsApiKey for your project.
				// See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
				'mapsApiKey': '{{ $google_maps_key }}'
			});

			google.charts.setOnLoadCallback(drawRegionsMap);

			function drawRegionsMap() {     

				let options = {
					colors: ['#007bff'],
					backgroundColor: 'transparent', // Make background transparent
					datalessRegionColor: 'rgba(32, 32, 50, 0.2)', // Light color for regions with no data
					defaultColor: '#007bff' // Default color for regions with data
				};
				let result = [];

				result.push(['Country', 'Users']);

				sessionData.map(function(row) { result.push([row[0], parseInt(row[1])]); });

				let data = google.visualization.arrayToDataTable(result);
				let chart = new google.visualization.GeoChart(document.getElementById('countries-analytics-chart'));
				chart.draw(data, options);
			}
		});		
	</script>
@endpush

