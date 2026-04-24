<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RedFlags</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" type="image/svg+xml" href="./img/logo.svg">
    <style>
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .new-log {
            animation: slideIn 0.5s ease-out;
            background: linear-gradient(90deg, rgba(34, 211, 238, 0.2) 0%, transparent 100%);
        }

        .pulse-dot {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .log-row {
            transition: all 0.2s ease;
        }

        .log-row:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .log-expanded {
            background: rgba(34, 211, 238, 0.1);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white min-h-screen flex flex-col">

    <header class="p-6 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <img src="./img/logo.svg" alt="Red Flags Logo" class="w-12 h-12">
            <h1 class="text-3xl font-bold tracking-wide">Red Flags</h1>
        </div>
        <nav class="space-x-6 text-lg">
            <a href="#" class="hover:text-cyan-400 transition">Home</a>
            <a href="./about" class="hover:text-cyan-400 transition">About</a>
        </nav>
    </header>

    <main class="flex-1 container mx-auto px-6 pt-4" x-data="mainComponent()">
        <div class="flex space-x-4 border-b border-white/10 pb-2 mb-6">
            <button @click="tab='raw-logs'"
                :class="tab==='raw-logs' ? 'border-cyan-400 text-cyan-400' : 'border-transparent'"
                class="px-4 pb-2 border-b-2 font-semibold transition">Pre-analysis Logs</button>
            <button @click="tab='logs'" :class="tab==='logs' ? 'border-cyan-400 text-cyan-400' : 'border-transparent'"
                class="px-4 pb-2 border-b-2 font-semibold transition">Analyzed Logs</button>
            <button @click="tab='analytics'"
                :class="tab==='analytics' ? 'border-cyan-400 text-cyan-400' : 'border-transparent'"
                class="px-4 pb-2 border-b-2 font-semibold transition">Analytics</button>
            <button @click="tab='anomaly-analytics'"
                :class="tab==='anomaly-analytics' ? 'border-cyan-400 text-cyan-400' : 'border-transparent'"
                class="px-4 pb-2 border-b-2 font-semibold transition">Anomaly Analytics</button>
        </div>

        <!-- INCIDENT LOGS TAB -->
        <section x-show="tab==='logs'" class="space-y-6" x-data="logsComponent()">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4 flex-wrap gap-2">
                    <input type="text" x-model="search" placeholder="Search logs..."
                        class="px-4 py-2 bg-white/10 rounded-lg w-80 focus:outline-none focus:ring-2 focus:ring-cyan-400" />

                    <select x-model="limit"
                        class="px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <template x-for="n in 10" :key="n">
                            <option :value="n*10" x-text="n*10" class="text-black bg-white"></option>
                        </template>
                    </select>

                    <select x-model="severity"
                        class="px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="">All Alert Levels</option>
                        <option value="CRITICAL">CRITICAL</option>
                        <option value="HIGH">HIGH</option>
                        <option value="MEDIUM">MEDIUM</option>
                        <option value="LOW">LOW</option>
                        <option value="INFO">INFO</option>
                    </select>

                    <select x-model="log_type"
                        class="px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="">All Types</option>
                        <option value="system">System</option>
                        <option value="web">Web</option>
                        <option value="application">Application</option>
                    </select>

                    <input type="number" x-model="hours" min="1" placeholder="Last N hours"
                        class="px-3 py-2 bg-white text-black rounded-lg shadow-md w-24 focus:outline-none focus:ring-2 focus:ring-cyan-400" />

                    <button @click="applyFilters()"
                        class="px-4 py-2 bg-cyan-500 hover:bg-cyan-600 text-white rounded-lg font-semibold transition">
                        Apply
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <div :class="autoRefresh ? 'bg-green-500' : 'bg-gray-500'"
                            class="w-2 h-2 rounded-full pulse-dot"></div>
                        <span class="text-sm text-white/70" x-text="autoRefresh ? 'Live' : 'Paused'"></span>
                    </div>
                    <button @click="toggleAutoRefresh()"
                        :class="autoRefresh ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'"
                        class="px-3 py-1 text-white text-sm rounded-lg font-semibold transition">
                        <span x-text="autoRefresh ? 'Pause' : 'Resume'"></span>
                    </button>
                    <span class="text-xs text-white/50" x-text="`Updated ${lastUpdate}`"></span>
                </div>
            </div>

            <div x-show="newLogsCount > 0"
                class="bg-cyan-500/20 border border-cyan-400 rounded-lg p-3 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i class="ph ph-bell-ringing text-cyan-400 text-xl"></i>
                    <span x-text="`${newLogsCount} new log${newLogsCount > 1 ? 's' : ''} received`"></span>
                </div>
                <button @click="scrollToTop()"
                    class="px-3 py-1 bg-cyan-500 hover:bg-cyan-600 rounded text-sm transition">
                    View
                </button>
            </div>

            <div x-ref="logsContainer"
                class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-6 h-[500px] overflow-y-auto border border-white/10">
                <template x-for="log in filteredLogs()" :key="log.id">
                    <div class="py-2 border-b border-white/10 flex flex-col md:flex-row md:items-center md:space-x-3 cursor-pointer hover:bg-white/5 transition"
                        :class="[getLogStyle(log).bg, isNewLog(log) ? 'new-log' : '', selectedLog?.id === log.id ? 'ring-2 ring-cyan-400' : '']"
                        @click="selectLog(log)">
                        <i :class="getLogStyle(log).icon" class="text-xl"></i>
                        <div class="flex-1">
                            <div class="flex items-center space-x-2">
                                <p class="text-white/90 font-medium" x-text="log.raw_log_message"></p>
                                <span x-show="isNewLog(log)"
                                    class="px-2 py-0.5 bg-cyan-500 text-white text-xs rounded-full">NEW</span>
                            </div>
                            <p class="text-white/50 text-sm mt-1 md:mt-0"
                                x-text="`Source: ${log.source_host} | Alert Level: ${log.severity} | ${formatTime(log.event_timestamp)}`">
                            </p>
                        </div>
                        <i class="ph ph-caret-right text-cyan-400 text-lg"></i>
                    </div>
                </template>

                <div x-show="filteredLogs().length === 0" class="text-center text-white/50 py-8">
                    <i class="ph ph-magnifying-glass text-4xl mb-2"></i>
                    <p>No logs found</p>
                </div>
            </div>

            <!-- Sidebar for incident details -->
            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed right-0 top-0 h-full w-full md:w-2/3 lg:w-1/2 bg-slate-900 shadow-2xl z-50 overflow-y-auto"
                @click.away="closeSidebar()">

                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <h2 class="text-2xl font-bold text-cyan-400">Incident Details</h2>
                        <button @click="closeSidebar()" class="text-white/70 hover:text-white transition">
                            <i class="ph ph-x text-2xl"></i>
                        </button>
                    </div>

                    <template x-if="selectedLog">
                        <div class="space-y-6">
                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Basic
                                    Information</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Incident ID:</span>
                                        <span class="text-white font-mono" x-text="selectedLog.id"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Created At:</span>
                                        <span class="text-white" x-text="formatTime(selectedLog.created_at)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Event Timestamp:</span>
                                        <span class="text-white"
                                            x-text="formatTime(selectedLog.event_timestamp)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Log
                                    Information</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Log Type:</span>
                                        <span
                                            class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded border border-blue-400"
                                            x-text="selectedLog.log_type"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Source Host:</span>
                                        <span
                                            class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded border border-purple-400"
                                            x-text="selectedLog.source_host"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Alert Level:</span>
                                        <span class="px-3 py-1 text-sm rounded border"
                                            :class="getSeverityColor(selectedLog.severity)"
                                            x-text="selectedLog.severity"></span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Is Anomaly:</span>
                                        <span class="px-3 py-1 text-sm rounded border"
                                            :class="selectedLog.is_anomaly ? 'bg-red-500/20 text-red-300 border-red-400' : 'bg-green-500/20 text-green-300 border-green-400'">
                                            <i :class="selectedLog.is_anomaly ? 'ph ph-warning-diamond' : 'ph ph-check-circle'"
                                                class="mr-1"></i>
                                            <span x-text="selectedLog.is_anomaly ? 'Yes' : 'No'"></span>
                                        </span>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Confidence:</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 text-sm rounded border"
                                                :class="getConfidenceColor(selectedLog.analysis_result?.confidence)">
                                                <span
                                                    x-text="selectedLog.analysis_result?.confidence ? (selectedLog.analysis_result.confidence * 100).toFixed(1) + '%' : 'N/A'"></span>
                                            </span>
                                            <div class="w-24 bg-white/10 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-500"
                                                    :class="getConfidenceBarColor(selectedLog.analysis_result?.confidence)"
                                                    :style="`width: ${(selectedLog.analysis_result?.confidence || 0) * 100}%`">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Raw Log
                                    Message</h3>
                                <p class="text-white bg-black/30 p-3 rounded font-mono text-sm break-words"
                                    x-text="selectedLog.raw_log_message"></p>
                            </div>

                            <template x-if="selectedLog.analysis_result">
                                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                    <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">
                                        Analysis Result</h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-start">
                                            <span class="text-white/50">Event Type:</span>
                                            <span class="px-3 py-1 text-sm rounded border"
                                                :class="getEventTypeColor(selectedLog.analysis_result.event_type)"
                                                x-text="selectedLog.analysis_result.event_type || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <span class="text-white/50 block mb-2">Description:</span>
                                            <p class="text-white bg-black/30 p-3 rounded text-sm"
                                                x-text="selectedLog.analysis_result.description || 'No description available'">
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/10">
                                            <div>
                                                <span class="text-white/50 text-xs block mb-1">Hostname:</span>
                                                <span class="text-white text-sm"
                                                    x-text="selectedLog.analysis_result.hostname || 'N/A'"></span>
                                            </div>
                                            <div>
                                                <span class="text-white/50 text-xs block mb-1">Service:</span>
                                                <span class="text-white text-sm"
                                                    x-text="selectedLog.analysis_result.service || 'N/A'"></span>
                                            </div>
                                            <div>
                                                <span class="text-white/50 text-xs block mb-1">User:</span>
                                                <span class="text-white text-sm"
                                                    x-text="selectedLog.analysis_result.user || 'N/A'"></span>
                                            </div>
                                            <div>
                                                <span class="text-white/50 text-xs block mb-1">Source IP:</span>
                                                <span class="text-white text-sm"
                                                    x-text="selectedLog.analysis_result.source_ip || 'N/A'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Full JSON
                                    Data</h3>
                                <pre
                                    class="bg-black/30 p-3 rounded text-xs text-green-300 font-mono overflow-x-auto max-h-64 overflow-y-auto"><code x-text="JSON.stringify(selectedLog, null, 2)"></code></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Overlay -->
            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeSidebar()" class="fixed inset-0 bg-black/50 z-40"></div>
        </section>

        <!-- RAW LOGS TAB -->
        <section x-show="tab==='raw-logs'" class="space-y-6" x-data="rawLogsComponent()">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-6 border border-white/10 space-y-4">
                        <h3 class="text-xl font-bold text-cyan-400 mb-4">Filters</h3>

                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-2">Number of recent logs</label>
                            <select x-model="n"
                                class="w-full px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-2">Log Type</label>
                            <select x-model="logType"
                                class="w-full px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                                <option value="">All Types</option>
                                <option value="system">System Logs</option>
                                <option value="web">Web Logs</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-2">Source Host</label>
                            <select x-model="sourceHost"
                                class="w-full px-3 py-2 bg-white text-black rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-cyan-400">
                                <option value="">All Hosts</option>
                                <template x-for="host in availableHosts" :key="host">
                                    <option :value="host" x-text="host"></option>
                                </template>
                            </select>
                        </div>

                        <button @click="fetchRawLogs()" :disabled="loading"
                            class="w-full px-4 py-2 bg-cyan-500 hover:bg-cyan-600 disabled:bg-gray-500 disabled:cursor-not-allowed text-white rounded-lg font-semibold transition">
                            <i :class="loading ? 'ph ph-spinner animate-spin' : 'ph ph-funnel'" class="mr-2"></i>
                            <span x-text="loading ? 'Loading...' : 'Apply Filters'"></span>
                        </button>

                        <button @click="exportToJSON()" :disabled="rawLogs.length === 0"
                            class="w-full px-4 py-2 bg-green-500 hover:bg-green-600 disabled:bg-gray-500 disabled:cursor-not-allowed text-white rounded-lg font-semibold transition">
                            <i class="ph ph-download-simple mr-2"></i>Export JSON
                        </button>

                        <div class="pt-4 border-t border-white/10">
                            <p class="text-sm text-white/50">Logs loaded: <span class="text-cyan-400 font-bold"
                                    x-text="rawLogs.length"></span></p>
                            <p class="text-sm text-white/50 mt-1">Last updated: <span class="text-white/70"
                                    x-text="lastUpdated"></span></p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-6 border border-white/10">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-white">Raw Log Messages</h3>
                            <div class="flex items-center space-x-2">
                                <button @click="viewMode = 'table'"
                                    :class="viewMode === 'table' ? 'bg-cyan-500' : 'bg-white/10'"
                                    class="px-3 py-1 rounded-lg transition">
                                    <i class="ph ph-table"></i>
                                </button>
                                <button @click="viewMode = 'json'"
                                    :class="viewMode === 'json' ? 'bg-cyan-500' : 'bg-white/10'"
                                    class="px-3 py-1 rounded-lg transition">
                                    <i class="ph ph-code"></i>
                                </button>
                            </div>
                        </div>

                        <div x-show="viewMode === 'table'" class="h-[600px] overflow-y-auto">
                            <div x-show="error" class="bg-red-500/20 border border-red-400 rounded-lg p-4 mb-4">
                                <div class="flex items-center space-x-2">
                                    <i class="ph ph-warning-circle text-red-400 text-xl"></i>
                                    <span class="text-red-300" x-text="error"></span>
                                </div>
                            </div>

                            <div x-show="loading" class="text-center text-white/50 py-12">
                                <i class="ph ph-spinner text-5xl mb-3 animate-spin"></i>
                                <p class="text-lg">Loading logs...</p>
                            </div>

                            <div x-show="!loading">
                                <template x-for="(log, index) in rawLogs" :key="index">
                                    <div class="log-row border-b border-white/10 py-3 cursor-pointer"
                                        :class="expandedLog === index ? 'log-expanded' : ''"
                                        @click="toggleExpand(index)">
                                        <div class="flex items-start space-x-3">
                                            <i :class="expandedLog === index ? 'ph-caret-down' : 'ph-caret-right'"
                                                class="ph text-cyan-400 text-lg mt-1"></i>
                                            <div class="flex-1">
                                                <p class="text-white font-medium" x-text="log.message"></p>

                                                <div x-show="expandedLog === index" x-transition
                                                    class="mt-3 space-y-2 pl-4 border-l-2 border-cyan-400">
                                                    <div class="flex items-center space-x-2">
                                                        <span
                                                            class="px-2 py-1 bg-blue-500/20 text-blue-300 text-xs rounded">
                                                            <i class="ph ph-tag mr-1"></i><span
                                                                x-text="log.log_type"></span>
                                                        </span>
                                                        <span
                                                            class="px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded">
                                                            <i class="ph ph-hard-drives mr-1"></i><span
                                                                x-text="log.source_host"></span>
                                                        </span>
                                                    </div>
                                                    <p class="text-white/50 text-sm">
                                                        <i class="ph ph-file-text mr-1"></i>
                                                        <span x-text="log.file_path"></span>
                                                    </p>
                                                    <p class="text-white/50 text-sm">
                                                        <i class="ph ph-clock mr-1"></i>
                                                        <span x-text="formatTime(log.timestamp)"></span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="rawLogs.length === 0 && !loading && !error"
                                    class="text-center text-white/50 py-12">
                                    <i class="ph ph-database text-5xl mb-3"></i>
                                    <p class="text-lg">No logs loaded</p>
                                    <p class="text-sm mt-2">Apply filters to fetch raw logs</p>
                                </div>
                            </div>
                        </div>

                        <div x-show="viewMode === 'json'" class="h-[600px] overflow-y-auto">
                            <pre
                                class="bg-black/30 p-4 rounded-lg text-sm text-green-300 font-mono"><code x-text="JSON.stringify(getJSONExport(), null, 2)"></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ANALYTICS TAB -->
        <section x-show="tab==='analytics'" class="space-y-6" x-data="analyticsComponent()">
            <div class="bg-white/5 p-4 rounded-xl border border-white/10">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-4">
                        <label class="text-white font-semibold">Time Range:</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" x-model.number="timeRange" min="1" :max="maxTimeRange"
                                class="px-3 py-2 bg-white/10 text-white rounded-lg w-24 focus:outline-none focus:ring-2 focus:ring-cyan-400"
                                placeholder="Hours" />
                            <span class="text-white/70">hours</span>
                        </div>
                        <span class="text-cyan-400 font-medium" x-text="`(${timeRangeLabel})`"></span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-white/50 text-sm mr-2">Quick select:</span>
                        <button @click="timeRange = 24" :class="timeRange === 24 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            24h
                        </button>
                        <button @click="timeRange = 168" :class="timeRange === 168 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            7d
                        </button>
                        <button @click="timeRange = 720" :class="timeRange === 720 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            30d
                        </button>
                        <button @click="timeRange = 8760" :class="timeRange === 8760 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            1y
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="bg-gradient-to-br from-cyan-500/20 to-cyan-600/10 p-6 rounded-xl border border-cyan-400/30 shadow-lg hover:shadow-xl transition">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">Total Incidents</h3>
                        <i class="ph ph-database text-cyan-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-cyan-400" x-text="allTimeStats.total_incidents || 0"></p>
                    <p class="text-xs text-white/50 mt-2">Last year (8760 hours)</p>
                </div>

                <div
                    class="bg-gradient-to-br from-red-500/20 to-red-600/10 p-6 rounded-xl border border-red-400/30 shadow-lg hover:shadow-xl transition">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">
                            Incidents Last <span x-text="timeRangeLabel"></span>
                        </h3>
                        <i class="ph ph-warning-circle text-red-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-red-400" x-text="stats.total_incidents || 0"></p>
                    <p class="text-xs text-white/50 mt-2">In selected time range</p>
                </div>

                <div
                    class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/10 p-6 rounded-xl border border-yellow-400/30 shadow-lg hover:shadow-xl transition">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">Top Source Host</h3>
                        <i class="ph ph-hard-drives text-yellow-400 text-2xl"></i>
                    </div>
                    <p class="text-2xl font-bold text-yellow-300 truncate"
                        x-text="stats.top_source_hosts?.[0]?.host || '-'"></p>
                    <p class="text-xs text-white/50 mt-2">
                        Count: <span x-text="stats.top_source_hosts?.[0]?.count || 0"></span>
                        <span class="text-white/30 mx-1">|</span>
                        Last <span x-text="timeRangeLabel"></span>
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white/5 p-6 rounded-xl border border-white/10 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i class="ph ph-chart-bar text-cyan-400 mr-2"></i>
                        Incidents by Alert Level
                        <span class="text-sm text-white/50 ml-2" x-text="`(Last ${timeRangeLabel})`"></span>
                    </h3>
                    <div class="h-64">
                        <canvas x-ref="severityChart"></canvas>
                    </div>
                </div>

                <div class="bg-white/5 p-6 rounded-xl border border-white/10 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i class="ph ph-chart-pie text-cyan-400 mr-2"></i>
                        Incidents by Log Type
                        <span class="text-sm text-white/50 ml-2" x-text="`(Last ${timeRangeLabel})`"></span>
                    </h3>
                    <div class="h-64">
                        <canvas x-ref="logTypeChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white/5 p-6 rounded-xl border border-white/10 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ph ph-list-bullets text-cyan-400 mr-2"></i>
                            Alert Level Breakdown
                        </div>
                        <span class="text-sm text-white/50" x-text="`Last ${timeRangeLabel}`"></span>
                    </h3>
                    <ul class="space-y-3">
                        <template x-for="(count, severity) in stats.by_severity" :key="severity">
                            <li class="flex justify-between items-center p-3 rounded-lg hover:bg-white/5 transition"
                                :class="getSeverityBgClass(severity)">
                                <div class="flex items-center space-x-3">
                                    <i :class="getSeverityIcon(severity)" class="text-xl"></i>
                                    <span class="font-medium" x-text="severity"></span>
                                </div>
                                <span class="font-bold text-lg" x-text="count"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="bg-white/5 p-6 rounded-xl border border-white/10 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="ph ph-files text-cyan-400 mr-2"></i>
                            Log Type Breakdown
                        </div>
                        <span class="text-sm text-white/50" x-text="`Last ${timeRangeLabel}`"></span>
                    </h3>
                    <ul class="space-y-3">
                        <template x-for="(count, logType) in stats.by_log_type" :key="logType">
                            <li
                                class="flex justify-between items-center p-3 rounded-lg bg-blue-500/10 border border-blue-400/20 hover:bg-blue-500/20 transition">
                                <div class="flex items-center space-x-3">
                                    <i class="ph ph-file-code text-blue-400 text-xl"></i>
                                    <span class="font-medium capitalize" x-text="logType"></span>
                                </div>
                                <span class="font-bold text-lg text-blue-300" x-text="count"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <div class="bg-white/5 p-6 rounded-xl border border-white/10 shadow-lg">
                <h3 class="text-lg font-semibold mb-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="ph ph-ranking text-cyan-400 mr-2"></i>
                        Top Source Hosts
                    </div>
                    <span class="text-sm text-white/50" x-text="`Last ${timeRangeLabel}`"></span>
                </h3>
                <div class="space-y-3">
                    <template x-for="(host, index) in stats.top_source_hosts?.slice(0, 10)" :key="host.host">
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex-shrink-0 w-8 h-8 rounded-full bg-cyan-500/20 flex items-center justify-center border border-cyan-400">
                                <span class="text-sm font-bold text-cyan-300" x-text="index + 1"></span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-white font-medium" x-text="host.host"></span>
                                    <span class="text-white/70 text-sm" x-text="host.count + ' incidents'"></span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-2 rounded-full transition-all duration-500"
                                        :style="`width: ${(host.count / (stats.top_source_hosts[0]?.count || 1)) * 100}%`">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <!-- ANOMALY ANALYTICS TAB -->
        <section x-show="tab==='anomaly-analytics'" class="space-y-6" x-data="anomalyAnalyticsComponent()">
            <div class="bg-white/5 p-4 rounded-xl border border-white/10">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center space-x-4">
                        <label class="text-white font-semibold">Time Range:</label>
                        <div class="flex items-center space-x-2">
                            <input type="number" x-model.number="timeRange" min="1" max="8760"
                                class="px-3 py-2 bg-white/10 text-white rounded-lg w-24 focus:outline-none focus:ring-2 focus:ring-cyan-400"
                                placeholder="Hours" />
                            <span class="text-white/70">hours</span>
                        </div>
                        <span class="text-cyan-400 font-medium" x-text="`(${timeRangeLabel})`"></span>
                    </div>

                    <!-- Quick Select Buttons -->
                    <div class="flex items-center space-x-2">
                        <span class="text-white/50 text-sm mr-2">Quick select:</span>
                        <button @click="timeRange = 24" :class="timeRange === 24 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            24h
                        </button>
                        <button @click="timeRange = 168" :class="timeRange === 168 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            7d
                        </button>
                        <button @click="timeRange = 720" :class="timeRange === 720 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            30d
                        </button>
                        <button @click="timeRange = 8760" :class="timeRange === 8760 ? 'bg-cyan-500' : 'bg-white/10'"
                            class="px-3 py-1 rounded-lg text-sm transition hover:bg-cyan-600">
                            1y
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div
                    class="bg-gradient-to-br from-red-500/20 to-red-600/10 p-6 rounded-xl border border-red-400/30 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">Total Anomalies</h3>
                        <i class="ph ph-warning-octagon text-red-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-red-400" x-text="anomalies.length"></p>
                    <p class="text-xs text-white/50 mt-2" x-text="`Last ${timeRangeLabel}`"></p>
                </div>

                <div
                    class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/10 p-6 rounded-xl border border-yellow-400/30 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">Avg Confidence</h3>
                        <i class="ph ph-chart-line text-yellow-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-yellow-400" x-text="averageConfidence + '%'"></p>
                    <p class="text-xs text-white/50 mt-2">Average detection confidence</p>
                </div>

                <div
                    class="bg-gradient-to-br from-purple-500/20 to-purple-600/10 p-6 rounded-xl border border-purple-400/30 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">High Confidence</h3>
                        <i class="ph ph-seal-check text-purple-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-purple-400" x-text="highConfidenceCount"></p>
                    <p class="text-xs text-white/50 mt-2">Confidence ≥ 80%</p>
                </div>

                <div
                    class="bg-gradient-to-br from-orange-500/20 to-orange-600/10 p-6 rounded-xl border border-orange-400/30 shadow-lg">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-white/70 uppercase tracking-wide">Critical Anomalies</h3>
                        <i class="ph ph-fire text-orange-400 text-2xl"></i>
                    </div>
                    <p class="text-4xl font-bold text-orange-400" x-text="criticalAnomaliesCount"></p>
                    <p class="text-xs text-white/50 mt-2">Critical severity level</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="text-white font-semibold">Sort by:</span>
                    <button @click="sortBy = 'confidence'; sortAnomalies()"
                        :class="sortBy === 'confidence' ? 'bg-cyan-500' : 'bg-white/10'"
                        class="px-4 py-2 rounded-lg text-sm transition hover:bg-cyan-600">
                        <i class="ph ph-trend-up mr-2"></i>Confidence
                    </button>
                    <button @click="sortBy = 'time'; sortAnomalies()"
                        :class="sortBy === 'time' ? 'bg-cyan-500' : 'bg-white/10'"
                        class="px-4 py-2 rounded-lg text-sm transition hover:bg-cyan-600">
                        <i class="ph ph-clock mr-2"></i>Time
                    </button>
                    <button @click="sortBy = 'severity'; sortAnomalies()"
                        :class="sortBy === 'severity' ? 'bg-cyan-500' : 'bg-white/10'"
                        class="px-4 py-2 rounded-lg text-sm transition hover:bg-cyan-600">
                        <i class="ph ph-warning-circle mr-2"></i>Severity
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <button @click="toggleSortOrder()"
                        class="px-4 py-2 bg-white/10 rounded-lg text-sm transition hover:bg-white/20">
                        <i :class="sortOrder === 'desc' ? 'ph ph-sort-descending' : 'ph ph-sort-ascending'"
                            class="mr-2"></i>
                        <span x-text="sortOrder === 'desc' ? 'Descending' : 'Ascending'"></span>
                    </button>
                    <span class="text-xs text-white/50">
                        Last updated: <span x-text="lastUpdate"></span>
                    </span>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-6 border border-white/10">
                <h3 class="text-xl font-bold mb-4 flex items-center">
                    <i class="ph ph-warning-diamond text-red-400 mr-2"></i>
                    Detected Anomalies
                    <span class="text-sm text-white/50 ml-3" x-text="`(${sortedAnomalies.length} total)`"></span>
                </h3>

                <div x-show="loading" class="text-center text-white/50 py-12">
                    <i class="ph ph-spinner text-5xl mb-3 animate-spin"></i>
                    <p class="text-lg">Loading anomalies...</p>
                </div>

                <div x-show="!loading" class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left text-white/70 font-semibold py-3 px-4">ID</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Message</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Confidence</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Severity</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Source Host</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Timestamp</th>
                                <th class="text-left text-white/70 font-semibold py-3 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="anomaly in sortedAnomalies" :key="anomaly.id">
                                <tr class="border-b border-white/10 hover:bg-white/5 transition cursor-pointer"
                                    @click="selectAnomaly(anomaly)">
                                    <td class="py-3 px-4">
                                        <span class="font-mono text-cyan-400" x-text="anomaly.id"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <p class="text-white/90 truncate max-w-md" x-text="anomaly.raw_log_message"></p>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs rounded font-semibold"
                                                :class="getConfidenceColor(anomaly.confidence || anomaly.analysis_result?.confidence)"
                                                x-text="((anomaly.confidence || anomaly.analysis_result?.confidence) * 100).toFixed(1) + '%'">
                                            </span>
                                            <div class="w-16 bg-white/10 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full"
                                                    :class="getConfidenceBarColor(anomaly.confidence || anomaly.analysis_result?.confidence)"
                                                    :style="`width: ${((anomaly.confidence || anomaly.analysis_result?.confidence) || 0) * 100}%`">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 text-xs rounded border"
                                            :class="getSeverityColor(anomaly.severity)"
                                            x-text="anomaly.severity"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-white/70" x-text="anomaly.source_host"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="text-white/50 text-sm"
                                            x-text="formatTime(anomaly.event_timestamp)"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <button @click.stop="selectAnomaly(anomaly)"
                                            class="px-3 py-1 bg-cyan-500 hover:bg-cyan-600 rounded text-sm transition">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>

                    <div x-show="sortedAnomalies.length === 0 && !loading" class="text-center text-white/50 py-12">
                        <i class="ph ph-check-circle text-5xl mb-3 text-green-400"></i>
                        <p class="text-lg">No anomalies detected in the selected time range</p>
                        <p class="text-sm mt-2">Try adjusting the time range or filters</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-6 border border-white/10">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold flex items-center">
                        <i class="ph ph-chart-bar-horizontal text-red-400 mr-2"></i>
                        Anomaly Template Distribution
                        <span class="text-sm text-white/50 ml-3" x-text="`(Last ${timeRangeLabel})`"></span>
                    </h3>

                    <!-- Filter & Sort Controls -->
                    <div class="flex items-center space-x-2">
                        <select x-model="chartFilter" @change="renderTemplateChart()"
                            class="px-3 py-1 bg-white/10 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            <option value="all">All Severities</option>
                            <option value="CRITICAL">Critical</option>
                            <option value="HIGH">High</option>
                            <option value="MEDIUM">Medium</option>
                            <option value="LOW">Low</option>
                            <option value="INFO">Info</option>
                        </select>

                        <select x-model="chartTopN" @change="renderTemplateChart()"
                            class="px-3 py-1 bg-white/10 text-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                            <option value="10">Top 10</option>
                            <option value="15">Top 15</option>
                            <option value="20">Top 20</option>
                            <option value="40">Top 40</option>
                        </select>
                    </div>
                </div>

                <div x-show="!loading && anomalies.length > 0" class="h-[600px]">
                    <canvas x-ref="templateChart"></canvas>
                </div>

                <div x-show="anomalies.length === 0 && !loading" class="text-center text-white/50 py-12">
                    <i class="ph ph-chart-bar text-4xl mb-2"></i>
                    <p>No anomaly data to display</p>
                </div>

                <!-- Legend -->
                <div x-show="!loading && anomalies.length > 0"
                    class="flex items-center justify-center space-x-6 mt-4 pt-4 border-t border-white/10">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded" style="background-color: #dc2626;"></div>
                        <span class="text-sm text-white/70">CRITICAL</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded" style="background-color: #ea580c;"></div>
                        <span class="text-sm text-white/70">HIGH</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded" style="background-color: #ca8a04;"></div>
                        <span class="text-sm text-white/70">MEDIUM</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded" style="background-color: #2563eb;"></div>
                        <span class="text-sm text-white/70">LOW</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 rounded" style="background-color: #6b7280;"></div>
                        <span class="text-sm text-white/70">INFO</span>
                    </div>
                    <div class="flex items-center space-x-4 ml-8 text-xs text-white/50">
                        <span>Color = dominant severity of template</span>
                    </div>
                </div>
            </div>

            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed right-0 top-0 h-full w-full md:w-2/3 lg:w-1/2 bg-slate-900 shadow-2xl z-50 overflow-y-auto"
                @click.away="closeSidebar()">

                <div class="p-6 space-y-6">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <h2 class="text-2xl font-bold text-red-400">
                            <i class="ph ph-warning-diamond mr-2"></i>Anomaly Details
                        </h2>
                        <button @click="closeSidebar()" class="text-white/70 hover:text-white transition">
                            <i class="ph ph-x text-2xl"></i>
                        </button>
                    </div>

                    <template x-if="selectedAnomaly">
                        <div class="space-y-6">
                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Basic
                                    Information</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Anomaly ID:</span>
                                        <span class="text-white font-mono" x-text="selectedAnomaly.id"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Created At:</span>
                                        <span class="text-white" x-text="formatTime(selectedAnomaly.created_at)"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-white/50">Event Timestamp:</span>
                                        <span class="text-white"
                                            x-text="formatTime(selectedAnomaly.event_timestamp)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Anomaly
                                    Information</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Confidence Score:</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-3 py-1 text-sm rounded border font-semibold"
                                                :class="getConfidenceColor(selectedAnomaly.confidence || selectedAnomaly.analysis_result?.confidence)">
                                                <span
                                                    x-text="((selectedAnomaly.confidence || selectedAnomaly.analysis_result?.confidence) * 100).toFixed(1) + '%'"></span>
                                            </span>
                                            <div class="w-32 bg-white/10 rounded-full h-3">
                                                <div class="h-3 rounded-full transition-all duration-500"
                                                    :class="getConfidenceBarColor(selectedAnomaly.confidence || selectedAnomaly.analysis_result?.confidence)"
                                                    :style="`width: ${((selectedAnomaly.confidence || selectedAnomaly.analysis_result?.confidence) || 0) * 100}%`">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Severity:</span>
                                        <span class="px-3 py-1 text-sm rounded border"
                                            :class="getSeverityColor(selectedAnomaly.severity)"
                                            x-text="selectedAnomaly.severity"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Source Host:</span>
                                        <span
                                            class="px-3 py-1 bg-purple-500/20 text-purple-300 text-sm rounded border border-purple-400"
                                            x-text="selectedAnomaly.source_host"></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-white/50">Log Type:</span>
                                        <span
                                            class="px-3 py-1 bg-blue-500/20 text-blue-300 text-sm rounded border border-blue-400"
                                            x-text="selectedAnomaly.log_type"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Raw Log
                                    Message</h3>
                                <p class="text-white bg-black/30 p-3 rounded font-mono text-sm break-words"
                                    x-text="selectedAnomaly.raw_log_message"></p>
                            </div>

                            <template x-if="selectedAnomaly.analysis_result">
                                <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                    <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">
                                        Analysis Result</h3>
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-start">
                                            <span class="text-white/50">Event Type:</span>
                                            <span class="px-3 py-1 text-sm rounded border"
                                                :class="getEventTypeColor(selectedAnomaly.analysis_result.event_type)"
                                                x-text="selectedAnomaly.analysis_result.event_type || 'N/A'"></span>
                                        </div>
                                        <div>
                                            <span class="text-white/50 block mb-2">Description:</span>
                                            <p class="text-white bg-black/30 p-3 rounded text-sm"
                                                x-text="selectedAnomaly.analysis_result.description || 'No description available'">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div class="bg-white/5 rounded-lg p-4 border border-white/10">
                                <h3 class="text-sm font-semibold text-white/70 mb-3 uppercase tracking-wide">Full JSON
                                    Data</h3>
                                <pre
                                    class="bg-black/30 p-3 rounded text-xs text-green-300 font-mono overflow-x-auto max-h-64 overflow-y-auto"><code x-text="JSON.stringify(selectedAnomaly, null, 2)"></code></pre>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Overlay -->
            <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" @click="closeSidebar()" class="fixed inset-0 bg-black/50 z-40"></div>
        </section>
    </main>

    <footer
        class="bg-white/5 dark:bg-gray-800 mt-10 py-6 border-t border-white/10 flex justify-between items-center px-6 rounded-t-xl">
        <p class="text-white/70">© <span id="year"></span> Red Flags Dashboard</p>
        <div class="flex items-center space-x-4">
            <img src="./img/ihu-logo-white.svg" alt="Logo 2" class="w-24 h-24">
        </div>
    </footer>

    <script>
        window.apiBaseUrl = "{{ config('api.base_url') }}";
        document.getElementById('year').textContent = new Date().getFullYear();

        document.addEventListener('alpine:init', () => {
            Alpine.data('mainComponent', () => ({
                tab: 'logs',
                init() {
                    this.$watch('tab', (value) => {
                        window.currentTab = value;
                    });
                    window.currentTab = this.tab;
                }
            }));

            Alpine.data('logsComponent', () => ({
                logs: [],
                search: '',
                severity: '',
                log_type: '',
                hours: '',
                limit: 20,
                autoRefresh: true,
                refreshInterval: null,
                lastUpdate: 'never',
                newLogsCount: 0,
                newLogIds: new Set(),
                selectedLog: null,
                sidebarOpen: false,

                init() {
                    console.log('Logs component initialized');
                    this.fetchLogs();
                    this.startAutoRefresh();

                    this.$watch('limit', () => {
                        console.log('Limit changed to:', this.limit);
                        this.applyFilters();
                    });

                    this.$watch('$el', () => {
                        return () => {
                            if (this.refreshInterval) {
                                clearInterval(this.refreshInterval);
                            }
                        };
                    });
                },

                getConfidenceColor(confidence) {
                    if (!confidence) return 'bg-gray-500/20 text-gray-300 border-gray-400';

                    const conf = confidence * 100;
                    if (conf >= 90) return 'bg-green-500/20 text-green-300 border-green-400';
                    if (conf >= 75) return 'bg-blue-500/20 text-blue-300 border-blue-400';
                    if (conf >= 50) return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                    return 'bg-orange-500/20 text-orange-300 border-orange-400';
                },

                getConfidenceBarColor(confidence) {
                    if (!confidence) return 'bg-gray-500';

                    const conf = confidence * 100;
                    if (conf >= 90) return 'bg-green-500';
                    if (conf >= 75) return 'bg-blue-500';
                    if (conf >= 50) return 'bg-yellow-500';
                    return 'bg-orange-500';
                },

                async fetchLogs() {
                    try {
                        let url = `${window.apiBaseUrl}/incidents?limit=${this.limit}&offset=0`;
                        if (this.severity) url += `&severity=${this.severity}`;
                        if (this.log_type) url += `&log_type=${this.log_type}`;
                        if (this.hours) url += `&hours=${this.hours}`;

                        console.log('Fetching logs from:', url);
                        const res = await fetch(url, {
                            headers: {
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        const data = await res.json();
                        const newLogs = data.incidents || [];

                        console.log('API returned', newLogs.length, 'logs');

                        if (this.logs.length > 0) {
                            const currentIds = new Set(this.logs.map(l => l.id));
                            const tempNewIds = new Set();
                            let count = 0;

                            newLogs.forEach(log => {
                                if (!currentIds.has(log.id)) {
                                    tempNewIds.add(log.id);
                                    count++;
                                }
                            });

                            this.newLogIds = tempNewIds;
                            this.newLogsCount = count;
                            console.log('Found', count, 'new logs');
                        }

                        this.logs = [...newLogs];
                        this.lastUpdate = new Date().toLocaleTimeString();
                        console.log('Logs updated. Total in state:', this.logs.length);

                        setTimeout(() => {
                            this.newLogIds = new Set();
                            this.newLogsCount = 0;
                        }, 10000);
                    } catch (e) {
                        console.error("Failed to fetch logs:", e);
                    }
                },

                applyFilters() {
                    this.newLogIds = new Set();
                    this.newLogsCount = 0;
                    this.fetchLogs();
                },

                startAutoRefresh() {
                    if (this.refreshInterval) {
                        clearInterval(this.refreshInterval);
                    }
                    this.refreshInterval = setInterval(() => {
                        if (this.autoRefresh && window.currentTab === 'logs') {
                            console.log('Auto-refreshing logs...');
                            this.fetchLogs();
                        }
                    }, 5000);
                },

                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                    console.log('Auto-refresh toggled:', this.autoRefresh);
                    if (this.autoRefresh) {
                        this.fetchLogs();
                    }
                },

                filteredLogs() {
                    const filtered = this.logs.filter(l =>
                        (!this.search || (l.raw_log_message || '').toLowerCase().includes(this.search.toLowerCase()))
                    );
                    return filtered;
                },

                isNewLog(log) {
                    return this.newLogIds.has(log.id);
                },

                scrollToTop() {
                    if (this.$refs.logsContainer) {
                        this.$refs.logsContainer.scrollTop = 0;
                    }
                    this.newLogsCount = 0;
                },

                selectLog(log) {
                    this.selectedLog = log;
                    this.sidebarOpen = true;
                },

                closeSidebar() {
                    this.sidebarOpen = false;
                    setTimeout(() => {
                        this.selectedLog = null;
                    }, 300);
                },

                getSeverityColor(severity) {
                    switch ((severity || '').toLowerCase()) {
                        case 'critical':
                            return 'bg-red-500/20 text-red-300 border-red-400';
                        case 'high':
                            return 'bg-orange-500/20 text-orange-300 border-orange-400';
                        case 'medium':
                            return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                        case 'low':
                            return 'bg-blue-500/20 text-blue-300 border-blue-400';
                        case 'info':
                            return 'bg-cyan-500/20 text-cyan-300 border-cyan-400';
                        default:
                            return 'bg-gray-500/20 text-gray-300 border-gray-400';
                    }
                },

                getEventTypeColor(eventType) {
                    switch ((eventType || '').toLowerCase()) {
                        case 'normal':
                            return 'bg-green-500/20 text-green-300 border-green-400';
                        case 'system_error':
                        case 'error':
                            return 'bg-red-500/20 text-red-300 border-red-400';
                        case 'warning':
                            return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                        case 'anomaly':
                            return 'bg-purple-500/20 text-purple-300 border-purple-400';
                        default:
                            return 'bg-blue-500/20 text-blue-300 border-blue-400';
                    }
                },

                formatTime(timestamp) {
                    if (!timestamp) return '';
                    const date = new Date(timestamp);
                    return date.toLocaleString();
                },

                getLogStyle(log) {
                    if (log.raw_log_message && log.raw_log_message.toLowerCase().includes('anomaly')) {
                        return { icon: 'ph ph-skull text-red-600', bg: 'bg-red-900/20' };
                    }

                    switch ((log.severity || '').toLowerCase()) {
                        case 'critical':
                        case 'high':
                            return { icon: 'ph ph-warning-circle text-red-400', bg: 'bg-red-800/20' };
                        case 'medium':
                            return { icon: 'ph ph-exclamation text-yellow-400', bg: 'bg-yellow-800/20' };
                        case 'low':
                        case 'info':
                            return { icon: 'ph ph-info text-cyan-400', bg: 'bg-cyan-800/20' };
                        default:
                            return { icon: 'ph ph-terminal text-green-400', bg: 'bg-green-800/20' };
                    }
                }
            }));

            Alpine.data('rawLogsComponent', () => ({
                rawLogs: [],
                n: 100,
                logType: '',
                sourceHost: '',
                availableHosts: [],
                expandedLog: null,
                viewMode: 'table',
                lastUpdated: 'never',
                loading: false,
                error: null,

                init() {
                    this.fetchHostsStats();
                },

                async fetchHostsStats() {
                    try {
                        const res = await fetch(`${window.apiBaseUrl}/raw-logs/stats?`, {
                            headers: {
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        const data = await res.json();
                        this.availableHosts = data.hosts || [];
                    } catch (e) {
                        console.error("Failed to fetch hosts stats:", e);
                        this.error = "Failed to load host statistics";
                    }
                },

                async fetchRawLogs() {
                    this.loading = true;
                    this.error = null;

                    try {
                        let url = `${window.apiBaseUrl}/raw-logs/recent?n=${this.n}`;
                        if (this.logType) url += `&log_type=${this.logType}`;
                        if (this.sourceHost) url += `&source_host=${this.sourceHost}`;

                        const res = await fetch(url, {
                            headers: {
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        const data = await res.json();

                        this.rawLogs = (data.logs || []).map(item => ({
                            message: item.message || '',
                            log_type: item.fields?.log_type || 'N/A',
                            source_host: item.fields?.source_host || 'N/A',
                            file_path: item.log?.file?.path || 'N/A',
                            timestamp: item['@timestamp'] || ''
                        }));

                        this.lastUpdated = new Date().toLocaleTimeString();
                        this.expandedLog = null;
                    } catch (e) {
                        console.error("Failed to fetch raw logs:", e);
                        this.error = "Failed to load raw logs";
                    } finally {
                        this.loading = false;
                    }
                },

                toggleExpand(index) {
                    this.expandedLog = this.expandedLog === index ? null : index;
                },

                formatTime(timestamp) {
                    if (!timestamp) return '';
                    const date = new Date(timestamp);
                    return date.toLocaleString();
                },

                getJSONExport() {
                    return this.rawLogs.map(log => ({
                        "Message": log.message,
                        "Log type": log.log_type,
                        "Source Host": log.source_host,
                        "Collection time": log.timestamp
                    }));
                },

                exportToJSON() {
                    const exportData = this.getJSONExport();
                    const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `raw-logs-export-${new Date().toISOString()}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }
            }));

            Alpine.data('templateAnalyticsComponent', () => ({
                templates: [],
                totalTemplates: 0,
                hours: 24,
                loading: false,
                templateChart: null,

                init() {
                    this.fetchTemplates();

                    this.$watch('hours', () => {
                        this.fetchTemplates();
                    });

                    // Auto-refresh every 60 seconds
                    setInterval(() => {
                        if (window.currentTab === 'template-analytics') {
                            this.fetchTemplates();
                        }
                    }, 60000);
                },

                async fetchTemplates() {
                    this.loading = true;
                    try {
                        const url = `${window.apiBaseUrl}/analyze/templates`;
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                hours: this.hours,
                                x_api_key: '-'
                            })
                        });

                        const data = await res.json();
                        this.totalTemplates = data.total_templates || 0;
                        this.templates = data.templates || [];

                        await this.$nextTick();
                        this.renderChart();
                    } catch (e) {
                        console.error("Failed to fetch templates:", e);
                    } finally {
                        this.loading = false;
                    }
                },

                renderChart() {
                    const ctx = this.$refs.templateChart;

                    if (!ctx || !ctx.getContext) {
                        console.log('Template chart canvas not ready yet');
                        return;
                    }

                    try {
                        if (this.templateChart) {
                            this.templateChart.destroy();
                        }

                        if (this.templates.length === 0) {
                            return;
                        }

                        const labels = this.templates.map(t => this.truncateTemplate(t.template));
                        const counts = this.templates.map(t => t.count);
                        const percentages = this.templates.map(t => t.percentage);

                        const colors = this.templates.map(t => this.getTemplateColor(t.severity_distribution));

                        this.templateChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Log Count',
                                    data: counts,
                                    backgroundColor: colors,
                                    borderColor: colors.map(c => c.replace('0.6', '1')),
                                    borderWidth: 2,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y', // Horizontal bar chart
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                        padding: 16,
                                        titleColor: '#fff',
                                        bodyColor: '#fff',
                                        borderColor: '#22d3ee',
                                        borderWidth: 2,
                                        displayColors: false,
                                        callbacks: {
                                            title: (context) => {
                                                const index = context[0].dataIndex;
                                                return this.templates[index].template;
                                            },
                                            label: (context) => {
                                                const index = context.dataIndex;
                                                const template = this.templates[index];
                                                return [
                                                    `Count: ${template.count}`,
                                                    `Percentage: ${template.percentage.toFixed(2)}%`,
                                                    '',
                                                    'Severity Distribution:',
                                                    ...Object.entries(template.severity_distribution).map(
                                                        ([sev, count]) => `  ${sev}: ${count}`
                                                    )
                                                ];
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)'
                                        },
                                        ticks: {
                                            color: '#fff'
                                        },
                                        title: {
                                            display: true,
                                            text: 'Count',
                                            color: '#fff'
                                        }
                                    },
                                    y: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            color: '#fff',
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Error rendering template chart:', e);
                    }
                },

                getTemplateColor(severityDistribution) {
                    let maxSeverity = 'INFO';
                    let maxCount = 0;

                    for (const [severity, count] of Object.entries(severityDistribution)) {
                        if (count > maxCount) {
                            maxCount = count;
                            maxSeverity = severity;
                        }
                    }

                    const colors = {
                        'CRITICAL': 'rgba(220, 38, 38, 0.6)',
                        'HIGH': 'rgba(234, 88, 12, 0.6)',
                        'MEDIUM': 'rgba(202, 138, 4, 0.6)',
                        'LOW': 'rgba(37, 99, 235, 0.6)',
                        'INFO': 'rgba(8, 145, 178, 0.6)'
                    };

                    return colors[maxSeverity] || colors['INFO'];
                },

                truncateTemplate(template, maxLength = 50) {
                    if (template.length <= maxLength) return template;
                    return template.substring(0, maxLength - 3) + '...';
                },

                get timeRangeLabel() {
                    if (this.hours == 24) return '24 hours';
                    if (this.hours == 168) return '7 days';
                    if (this.hours == 720) return '30 days';
                    if (this.hours == 8760) return '1 year';
                    if (this.hours < 24) return `${this.hours} hours`;
                    if (this.hours < 168) return `${Math.round(this.hours / 24)} days`;
                    if (this.hours < 720) return `${Math.round(this.hours / 168)} weeks`;
                    return `${Math.round(this.hours / 720)} months`;
                },

                get totalLogs() {
                    return this.templates.reduce((sum, t) => sum + t.count, 0);
                },

                get mostCommonTemplate() {
                    if (this.templates.length === 0) return 'N/A';
                    return this.truncateTemplate(this.templates[0].template, 40);
                },

                get mostCommonPercentage() {
                    if (this.templates.length === 0) return 0;
                    return this.templates[0].percentage.toFixed(2);
                }
            }));

            Alpine.data('anomalyAnalyticsComponent', () => ({
                anomalies: [],
                sortedAnomalies: [],
                templates: [],
                selectedAnomaly: null,
                sidebarOpen: false,
                timeRange: 24,
                sortBy: 'confidence',
                sortOrder: 'desc',
                loading: false,
                lastUpdate: 'never',
                templateChart: null,
                chartFilter: 'all',
                chartTopN: 15,

                init() {
                    this.fetchAnomalies();
                    this.fetchTemplates();

                    this.$watch('timeRange', () => {
                        this.fetchAnomalies();
                        this.fetchTemplates();
                    });

                    setInterval(() => {
                        if (window.currentTab === 'anomaly-analytics') {
                            this.fetchAnomalies();
                            this.fetchTemplates();
                        }
                    }, 300000);
                },

                async fetchAnomalies() {
                    this.loading = true;
                    try {
                        const url = `${window.apiBaseUrl}/incidents?hours=${this.timeRange}&limit=100&offset=0&x_api_key=-`;
                        const res = await fetch(url);
                        const data = await res.json();

                        this.anomalies = (data.incidents || []).filter(incident => {
                            return incident.is_anomaly === true ||
                                incident.analysis_result?.is_anomaly === true;
                        });

                        this.sortAnomalies();
                        this.lastUpdate = new Date().toLocaleTimeString();
                    } catch (e) {
                        console.error("Failed to fetch anomalies:", e);
                    } finally {
                        this.loading = false;
                    }
                },

                async fetchTemplates() {
                    try {
                        const url = `${window.apiBaseUrl}/analyze/templates`;
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                hours: this.timeRange,
                                x_api_key: '-'
                            })
                        });

                        const data = await res.json();
                        this.templates = data.templates || [];

                        await this.$nextTick();
                        if (window.currentTab === 'anomaly-analytics') {
                            setTimeout(() => {
                                this.renderTemplateChart();
                            }, 100);
                        }
                    } catch (e) {
                        console.error("Failed to fetch templates:", e);
                    }
                },

                renderTemplateChart() {
                    const ctx = this.$refs.templateChart;

                    if (!ctx || !ctx.getContext) {
                        console.log('Template chart canvas not ready');
                        return;
                    }

                    if (this.templates.length === 0) {
                        console.log('No templates to render');
                        return;
                    }

                    try {
                        if (this.templateChart) {
                            this.templateChart.destroy();
                            this.templateChart = null;
                        }

                        let filteredTemplates = [...this.templates];

                        if (this.chartFilter !== 'all') {
                            filteredTemplates = filteredTemplates.filter(t =>
                                t.severity_distribution && t.severity_distribution[this.chartFilter] > 0
                            );
                        }

                        filteredTemplates = filteredTemplates.slice(0, parseInt(this.chartTopN));

                        if (filteredTemplates.length === 0) {
                            console.log('No templates after filtering');
                            return;
                        }

                        const labels = filteredTemplates.map(t => this.truncateTemplate(t.template, 60));
                        const counts = filteredTemplates.map(t => t.count);
                        const colors = filteredTemplates.map(t => this.getTemplateDominantColor(t.severity_distribution));

                        this.templateChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Log Count',
                                    data: counts,
                                    backgroundColor: colors,
                                    borderColor: colors.map(c => c.replace('0.8', '1')),
                                    borderWidth: 2,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                indexAxis: 'y',
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.9)',
                                        padding: 16,
                                        titleColor: '#fff',
                                        bodyColor: '#fff',
                                        borderColor: '#22d3ee',
                                        borderWidth: 2,
                                        displayColors: false,
                                        callbacks: {
                                            title: (context) => {
                                                const index = context[0].dataIndex;
                                                return filteredTemplates[index].template;
                                            },
                                            label: (context) => {
                                                const index = context.dataIndex;
                                                const template = filteredTemplates[index];

                                                return [
                                                    `Count: ${template.count}`,
                                                    `Percentage: ${template.percentage.toFixed(2)}%`,
                                                    '',
                                                    'Severity Distribution:',
                                                    ...Object.entries(template.severity_distribution || {})
                                                        .sort(([, a], [, b]) => b - a)
                                                        .map(([sev, count]) => `  ${sev}: ${count}`)
                                                ];
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)'
                                        },
                                        ticks: {
                                            color: '#fff',
                                            font: { size: 11 }
                                        },
                                        title: {
                                            display: true,
                                            text: 'Number of Logs',
                                            color: '#fff',
                                            font: { size: 12 }
                                        }
                                    },
                                    y: {
                                        grid: { display: false },
                                        ticks: {
                                            color: '#fff',
                                            font: { size: 10 },
                                            autoSkip: false
                                        }
                                    }
                                }
                            }
                        });

                        console.log('Template chart rendered successfully with', filteredTemplates.length, 'templates');
                    } catch (e) {
                        console.error('Error rendering template chart:', e);
                        if (this.templateChart) {
                            try {
                                this.templateChart.destroy();
                            } catch (destroyError) {
                                console.warn('Error destroying chart:', destroyError);
                            }
                            this.templateChart = null;
                        }
                    }
                },

                getTemplateDominantColor(severityDistribution) {
                    if (!severityDistribution) return 'rgba(107, 114, 128, 0.8)'; // INFO color

                    // Find the severity with the highest count
                    let maxCount = 0;
                    let dominantSeverity = 'INFO';

                    const severityPriority = ['CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO'];

                    for (const severity of severityPriority) {
                        const count = severityDistribution[severity] || 0;
                        if (count > maxCount) {
                            maxCount = count;
                            dominantSeverity = severity;
                        }
                    }

                    const colors = {
                        'CRITICAL': 'rgba(220, 38, 38, 0.8)',
                        'HIGH': 'rgba(234, 88, 12, 0.8)',
                        'MEDIUM': 'rgba(202, 138, 4, 0.8)',
                        'LOW': 'rgba(37, 99, 235, 0.8)',
                        'INFO': 'rgba(107, 114, 128, 0.8)'
                    };

                    return colors[dominantSeverity] || colors['INFO'];
                },

                truncateTemplate(template, maxLength = 60) {
                    if (!template) return 'Unknown';
                    if (template.length <= maxLength) return template;
                    return template.substring(0, maxLength - 3) + '...';
                },

                sortAnomalies() {
                    let sorted = [...this.anomalies];

                    if (this.sortBy === 'confidence') {
                        sorted.sort((a, b) => {
                            const confA = a.confidence || a.analysis_result?.confidence || 0;
                            const confB = b.confidence || b.analysis_result?.confidence || 0;
                            return this.sortOrder === 'desc' ? confB - confA : confA - confB;
                        });
                    } else if (this.sortBy === 'time') {
                        sorted.sort((a, b) => {
                            const timeA = new Date(a.event_timestamp || a.created_at);
                            const timeB = new Date(b.event_timestamp || b.created_at);
                            return this.sortOrder === 'desc' ? timeB - timeA : timeA - timeB;
                        });
                    } else if (this.sortBy === 'severity') {
                        const severityOrder = { 'CRITICAL': 5, 'HIGH': 4, 'MEDIUM': 3, 'LOW': 2, 'INFO': 1 };
                        sorted.sort((a, b) => {
                            const sevA = severityOrder[a.severity] || 0;
                            const sevB = severityOrder[b.severity] || 0;
                            return this.sortOrder === 'desc' ? sevB - sevA : sevA - sevB;
                        });
                    }

                    this.sortedAnomalies = sorted;
                },

                toggleSortOrder() {
                    this.sortOrder = this.sortOrder === 'desc' ? 'asc' : 'desc';
                    this.sortAnomalies();
                },

                selectAnomaly(anomaly) {
                    this.selectedAnomaly = anomaly;
                    this.sidebarOpen = true;
                },

                closeSidebar() {
                    this.sidebarOpen = false;
                    setTimeout(() => {
                        this.selectedAnomaly = null;
                    }, 300);
                },

                get timeRangeLabel() {
                    if (this.timeRange == 24) return '24 hours';
                    if (this.timeRange == 168) return '7 days';
                    if (this.timeRange == 720) return '30 days';
                    if (this.timeRange == 8760) return '1 year';
                    if (this.timeRange < 24) return `${this.timeRange} hours`;
                    if (this.timeRange < 168) return `${Math.round(this.timeRange / 24)} days`;
                    if (this.timeRange < 720) return `${Math.round(this.timeRange / 168)} weeks`;
                    return `${Math.round(this.timeRange / 720)} months`;
                },

                get averageConfidence() {
                    if (this.anomalies.length === 0) return 0;
                    const sum = this.anomalies.reduce((acc, a) => {
                        const conf = a.confidence || a.analysis_result?.confidence || 0;
                        return acc + conf;
                    }, 0);
                    return ((sum / this.anomalies.length) * 100).toFixed(1);
                },

                get highConfidenceCount() {
                    return this.anomalies.filter(a => {
                        const conf = a.confidence || a.analysis_result?.confidence || 0;
                        return conf >= 0.8;
                    }).length;
                },

                get criticalAnomaliesCount() {
                    return this.anomalies.filter(a => a.severity === 'CRITICAL').length;
                },

                getConfidenceColor(confidence) {
                    if (!confidence) return 'bg-gray-500/20 text-gray-300 border-gray-400';

                    const conf = confidence * 100;
                    if (conf >= 90) return 'bg-green-500/20 text-green-300 border-green-400';
                    if (conf >= 75) return 'bg-blue-500/20 text-blue-300 border-blue-400';
                    if (conf >= 50) return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                    return 'bg-orange-500/20 text-orange-300 border-orange-400';
                },

                getConfidenceBarColor(confidence) {
                    if (!confidence) return 'bg-gray-500';

                    const conf = confidence * 100;
                    if (conf >= 90) return 'bg-green-500';
                    if (conf >= 75) return 'bg-blue-500';
                    if (conf >= 50) return 'bg-yellow-500';
                    return 'bg-orange-500';
                },

                getSeverityColor(severity) {
                    switch ((severity || '').toLowerCase()) {
                        case 'critical':
                            return 'bg-red-500/20 text-red-300 border-red-400';
                        case 'high':
                            return 'bg-orange-500/20 text-orange-300 border-orange-400';
                        case 'medium':
                            return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                        case 'low':
                            return 'bg-blue-500/20 text-blue-300 border-blue-400';
                        case 'info':
                            return 'bg-cyan-500/20 text-cyan-300 border-cyan-400';
                        default:
                            return 'bg-gray-500/20 text-gray-300 border-gray-400';
                    }
                },

                getEventTypeColor(eventType) {
                    switch ((eventType || '').toLowerCase()) {
                        case 'normal':
                            return 'bg-green-500/20 text-green-300 border-green-400';
                        case 'system_error':
                        case 'error':
                            return 'bg-red-500/20 text-red-300 border-red-400';
                        case 'warning':
                            return 'bg-yellow-500/20 text-yellow-300 border-yellow-400';
                        case 'anomaly':
                            return 'bg-purple-500/20 text-purple-300 border-purple-400';
                        default:
                            return 'bg-blue-500/20 text-blue-300 border-blue-400';
                    }
                },

                formatTime(timestamp) {
                    if (!timestamp) return '';
                    const date = new Date(timestamp);
                    return date.toLocaleString();
                }
            }));

            Alpine.data('analyticsComponent', () => ({
                stats: {},
                allTimeStats: {},
                severityChart: null,
                logTypeChart: null,
                timeRange: 24, // Default is 24 hours
                maxTimeRange: 8760, // 1 year in hours

                init() {
                    this.fetchStats();
                    this.fetchAllTimeStats();

                    // Auto-refresh analytics every 30 seconds
                    setInterval(() => {
                        this.fetchStats();
                        this.fetchAllTimeStats();
                    }, 30000);

                    this.$watch('timeRange', () => {
                        this.fetchStats();
                    });
                },

                async fetchAllTimeStats() {
                    try {
                        const res = await fetch(`${window.apiBaseUrl}/statistics?&hours=${this.maxTimeRange}`, {
                            headers: {
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        const data = await res.json();
                        this.allTimeStats = data || {};
                    } catch (e) {
                        console.error("Failed to fetch all-time statistics:", e);
                    }
                },

                async fetchStats() {
                    try {
                        const res = await fetch(`${window.apiBaseUrl}/statistics?&hours=${this.timeRange}`, {
                            headers: {
                                'ngrok-skip-browser-warning': 'true'
                            }
                        });
                        const data = await res.json();
                        this.stats = data || {};

                        await this.$nextTick();
                        this.renderCharts();
                    } catch (e) {
                        console.error("Failed to fetch statistics:", e);
                    }
                },

                get timeRangeLabel() {
                    if (this.timeRange == 24) return '24 hours';
                    if (this.timeRange == 168) return '7 days';
                    if (this.timeRange == 720) return '30 days';
                    if (this.timeRange == 8760) return '1 year';
                    if (this.timeRange < 24) return `${this.timeRange} hours`;
                    if (this.timeRange < 168) return `${Math.round(this.timeRange / 24)} days`;
                    if (this.timeRange < 720) return `${Math.round(this.timeRange / 168)} weeks`;
                    return `${Math.round(this.timeRange / 720)} months`;
                },

                renderCharts() {
                    this.renderSeverityChart();
                    this.renderLogTypeChart();
                },

                renderSeverityChart() {
                    const ctx = this.$refs.severityChart;
                    if (!ctx) return;

                    if (this.severityChart) {
                        this.severityChart.destroy();
                    }

                    const severityData = this.stats.by_severity || {};
                    const labels = Object.keys(severityData);
                    const data = Object.values(severityData);

                    const colors = {
                        'CRITICAL': '#dc2626',
                        'HIGH': '#ea580c',
                        'MEDIUM': '#ca8a04',
                        'LOW': '#2563eb',
                        'INFO': '#0891b2'
                    };

                    const backgroundColors = labels.map(label => colors[label] || '#6b7280');

                    this.severityChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Incidents',
                                data: data,
                                backgroundColor: backgroundColors,
                                borderColor: backgroundColors.map(c => c + 'CC'),
                                borderWidth: 2,
                                borderRadius: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: '#22d3ee',
                                    borderWidth: 1
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(255, 255, 255, 0.1)'
                                    },
                                    ticks: {
                                        color: '#fff'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#fff'
                                    }
                                }
                            }
                        }
                    });
                },

                renderLogTypeChart() {
                    const ctx = this.$refs.logTypeChart;
                    if (!ctx) return;

                    if (this.logTypeChart) {
                        this.logTypeChart.destroy();
                    }

                    const logTypeData = this.stats.by_log_type || {};
                    const labels = Object.keys(logTypeData);
                    const data = Object.values(logTypeData);

                    const colors = ['#22d3ee', '#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b'];

                    this.logTypeChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: labels.map(l => l.charAt(0).toUpperCase() + l.slice(1)),
                            datasets: [{
                                data: data,
                                backgroundColor: colors,
                                borderColor: '#1e293b',
                                borderWidth: 3,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#fff',
                                        padding: 15,
                                        font: {
                                            size: 12
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                    padding: 12,
                                    titleColor: '#fff',
                                    bodyColor: '#fff',
                                    borderColor: '#22d3ee',
                                    borderWidth: 1
                                }
                            }
                        }
                    });
                },

                getSeverityBgClass(severity) {
                    const classes = {
                        'CRITICAL': 'bg-red-500/10 border border-red-400/20',
                        'HIGH': 'bg-orange-500/10 border border-orange-400/20',
                        'MEDIUM': 'bg-yellow-500/10 border border-yellow-400/20',
                        'LOW': 'bg-blue-500/10 border border-blue-400/20',
                        'INFO': 'bg-cyan-500/10 border border-cyan-400/20'
                    };
                    return classes[severity] || 'bg-gray-500/10 border border-gray-400/20';
                },

                getSeverityIcon(severity) {
                    const icons = {
                        'CRITICAL': 'ph ph-warning-octagon text-red-400',
                        'HIGH': 'ph ph-warning-circle text-orange-400',
                        'MEDIUM': 'ph ph-warning text-yellow-400',
                        'LOW': 'ph ph-info text-blue-400',
                        'INFO': 'ph ph-info-circle text-cyan-400'
                    };
                    return icons[severity] || 'ph ph-circle text-gray-400';
                }
            }));
        });
    </script>

</body>

</html>
