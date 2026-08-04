<!-- Notation Guide Button -->
<button type="button" data-modal-target="NotationGuideModal" data-modal-toggle="NotationGuideModal"
  class="fixed bottom-20 right-4 md:bottom-24 md:right-6 lg:bottom-28 lg:right-8
            bg-primary-500 hover:bg-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400
            text-white rounded-full p-3 md:p-4 shadow-lg hover:shadow-xl
            transition-all duration-300 hover:scale-110 z-50 group"
  title="Notation & Subject Guide">
  <svg xmlns="http://www.w3.org/2000/svg" 
    class="h-6 w-6 md:h-7 md:w-7" 
    fill="none" 
    viewBox="0 0 24 24" 
    stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
  </svg>
  <span class="absolute right-full mr-3 top-1/2 -translate-y-1/2
                 bg-gray-800 dark:bg-gray-700 text-white text-sm
                 px-3 py-2 rounded whitespace-nowrap opacity-0
                 group-hover:opacity-100 transition-opacity duration-300
                 pointer-events-none shadow-lg hidden md:block">
    Notation & Subject Guide
  </span>
</button>

<!-- Notation Guide Modal -->
<div id="NotationGuideModal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[100%] max-h-full bg-gray-900 bg-opacity-50">
    <div class="relative p-4 w-full max-w-7xl max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 flex flex-col max-h-[90vh]">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 bg-gradient-to-r from-primary-700 to-primary-800 text-white">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Notation & Subject Guide
                </h3>
                <button type="button" class="text-white hover:bg-primary-600 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center transition-colors" data-modal-hide="NotationGuideModal">
                    <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <!-- Modal body -->
            <div class="p-4 md:p-5 space-y-6 overflow-y-auto">
                @php
                    $cutterData = [];
                    $ddcData = [];
                    if (file_exists(storage_path('app/public/cutter_notation.json'))) {
                        $cutterData = json_decode(file_get_contents(storage_path('app/public/cutter_notation.json')), true);
                    }
                    if (file_exists(storage_path('app/public/ddc.json'))) {
                        $ddcData = json_decode(file_get_contents(storage_path('app/public/ddc.json')), true);
                    }
                @endphp

                <!-- Cutter's Notation -->
                @if(!empty($cutterData))
                <div class="mb-8">
                    <h4 class="text-xl font-bold text-center text-gray-900 dark:text-white uppercase mb-6">{{ $cutterData['title'] ?? "CUTTER'S NOTATION" }}</h4>
                    <div class="overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600">
                            <thead class="text-xs text-gray-700 bg-gray-50 dark:bg-gray-700 dark:text-gray-400 text-center">
                                <tr>
                                    <th scope="col" class="px-6 py-3 border-r border-gray-300 dark:border-gray-600 w-1/4">
                                        <!-- Empty header for the rules column -->
                                    </th>
                                    @foreach(range(2, 9) as $num)
                                    <th scope="col" class="px-2 py-3 border-r border-gray-300 dark:border-gray-600 font-bold">
                                        {{ $num }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cutterData['rules'] as $rule)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 dark:text-white border-r border-gray-300 dark:border-gray-600">
                                        <div class="font-bold mb-1">{{ $rule['label'] }}</div>
                                        <div class="text-xs font-normal text-gray-600 dark:text-gray-300">
                                            {{ $rule['description'] }}
                                        </div>
                                        @if(isset($rule['exceptions']))
                                        <div class="text-xs font-normal text-gray-600 dark:text-gray-300 mt-1 italic">
                                            Exceptions: @foreach($rule['exceptions'] as $ex) {{ $ex['pattern'] }} (use {{ $ex['number'] }}) @endforeach
                                        </div>
                                        @endif
                                        @if(isset($rule['special_cases']))
                                        <div class="text-xs font-normal text-gray-600 dark:text-gray-300 mt-1 italic">
                                            Special cases: @foreach($rule['special_cases'] as $sc) {{ $sc['pattern'] }} (use {{ $sc['number_range'] }}) @endforeach
                                        </div>
                                        @endif
                                    </th>
                                    @foreach(range(2, 9) as $num)
                                        @php
                                            $cellData = collect($rule['table'] ?? [])->firstWhere('number', $num);
                                        @endphp
                                        <td class="px-2 py-4 text-center border-r border-gray-300 dark:border-gray-600 align-top">
                                            @if($cellData)
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $cellData['range'] }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $num }}</div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <hr class="h-px my-8 bg-gray-200 border-0 dark:bg-gray-700">

                <!-- DDC Scheme -->
                @if(!empty($ddcData))
                <div>
                    <h4 class="text-xl font-bold text-center text-gray-900 dark:text-white uppercase mb-2">{{ $ddcData['title'] ?? 'DEVISED DDC SCHEME AND SUBJECT ACCESS CODES TOOL' }}</h4>
                    @if(isset($ddcData['sections']))
                        @foreach($ddcData['sections'] as $section)
                            <h5 class="text-lg font-bold text-center text-gray-800 dark:text-gray-200 uppercase mb-6">FOR {{ $section['label'] }}</h5>
                            
                            <div class="overflow-x-auto shadow-md sm:rounded-lg mb-6">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-300 dark:border-gray-600">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 border-r border-gray-300 dark:border-gray-600 font-bold underline">DDC</th>
                                            <th scope="col" class="px-6 py-3 border-r border-gray-300 dark:border-gray-600 font-bold underline">Subject(s)</th>
                                            <th scope="col" class="px-6 py-3 font-bold underline">Subject Access Code</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($section['entries'] as $entry)
                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 align-top">
                                            <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap border-r border-gray-300 dark:border-gray-600">
                                                {{ $entry['ddc'] }}
                                            </td>
                                            <td class="px-6 py-4 border-r border-gray-300 dark:border-gray-600">
                                                {{ $entry['subject'] }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <ul class="list-none space-y-1">
                                                    @if(isset($entry['subject_access_codes']))
                                                        @foreach($entry['subject_access_codes'] as $code)
                                                            <li>{{ $code }}</li>
                                                        @endforeach
                                                    @endif
                                                    
                                                    @if(isset($entry['optional_subject_access_codes']) && count($entry['optional_subject_access_codes']) > 0)
                                                        <li class="mt-4 font-semibold text-gray-700 dark:text-gray-300">Optional</li>
                                                        <li>
                                                            <div class="relative pl-4 border-l-2 border-gray-300 dark:border-gray-600 py-2">
                                                                <ul class="list-none space-y-1">
                                                                @foreach($entry['optional_subject_access_codes'] as $optCode)
                                                                    <li>{{ $optCode }}</li>
                                                                @endforeach
                                                                </ul>
                                                            </div>
                                                        </li>
                                                    @endif
                                                </ul>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>
                @endif
                
            </div>
            
            <!-- Modal footer -->
            <div class="flex items-center justify-end p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                <button data-modal-hide="NotationGuideModal" type="button" class="text-white bg-primary-500 hover:bg-primary-600 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800 transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>
