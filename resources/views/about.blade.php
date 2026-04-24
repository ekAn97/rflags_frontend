<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - RedFlags</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="icon" type="image/svg+xml" href="./img/logo.svg">
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
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
            <a href="./" class="hover:text-cyan-400 transition">Home</a>
            <a href="#" class="text-cyan-400 border-b-2 border-cyan-400 pb-1">About</a>
        </nav>
    </header>

    <main class="flex-1 container mx-auto px-6 pt-10 max-w-4xl animate-fade-in">
        <div class="bg-white/5 backdrop-blur-lg rounded-xl shadow-xl p-10 border border-white/10 space-y-8">
            <div class="border-b border-white/10 pb-6">
                <h2 class="text-4xl font-extrabold text-cyan-400 tracking-wide mb-2">About This Project</h2>
                <p class="text-white/50 text-sm">Real-time security monitoring and anomaly detection</p>
            </div>

            <div class="space-y-6">
                <div class="flex items-start space-x-4">
                    <i class="ph ph-monitor text-cyan-400 text-3xl mt-1"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-white mb-2">Modern Dashboard Interface</h3>
                        <p class="text-white/80 leading-relaxed">
                            The Red Flags Dashboard is a modern UI built to communicate with an external Python-based
                            analysis system via API calls. Its purpose is to help visualize logs, detect suspicious
                            patterns, and present analytics in a clear and elegant interface.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <i class="ph ph-cpu text-cyan-400 text-3xl mt-1"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-white mb-2">Powered by Machine Learning</h3>
                        <p class="text-white/80 leading-relaxed">
                            Powered by Laravel on the frontend and a custom machine-learning backend, this platform
                            provides a seamless and fast workflow for monitoring, reviewing, and understanding log data
                            in real time.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-4">
                    <i class="ph ph-lightning text-cyan-400 text-3xl mt-1"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-white mb-2">Real-Time Monitoring</h3>
                        <p class="text-white/80 leading-relaxed">
                            Experience live log updates with automatic refresh capabilities, intelligent filtering,
                            and instant anomaly detection to keep your systems secure and monitored 24/7.
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6">
                <div class="bg-white/5 p-4 rounded-lg text-center border border-white/10">
                    <i class="ph ph-shield-check text-4xl text-green-400 mb-2"></i>
                    <h4 class="font-semibold text-white mb-1">Secure</h4>
                    <p class="text-sm text-white/60">Enterprise-grade security monitoring</p>
                </div>
                <div class="bg-white/5 p-4 rounded-lg text-center border border-white/10">
                    <i class="ph ph-clock text-4xl text-blue-400 mb-2"></i>
                    <h4 class="font-semibold text-white mb-1">Real-Time</h4>
                    <p class="text-sm text-white/60">Live updates every 5 seconds</p>
                </div>
                <div class="bg-white/5 p-4 rounded-lg text-center border border-white/10">
                    <i class="ph ph-brain text-4xl text-purple-400 mb-2"></i>
                    <h4 class="font-semibold text-white mb-1">Intelligent</h4>
                    <p class="text-sm text-white/60">ML-powered anomaly detection</p>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer
        class="bg-white/5 dark:bg-gray-800 mt-10 py-6 border-t border-white/10 flex justify-between items-center px-6 rounded-t-xl">
        <p class="text-white/70">© <span id="year"></span> Red Flags Dashboard</p>
        <div class="flex items-center space-x-4">
            <img src="./img/ihu-logo-white.svg" alt="IHU Logo" class="w-24 h-24">
            <!-- <img src="./img/logo-secureu-white.svg" alt="Logo 1" class="w-24 h-24"> -->
            <!-- <img src="./img/intersoc.svg" alt="Logo 2" class="w-24 h-24">
            <img src="./img/cyberguard.png" alt="Logo 2" class="w-42 h-12"> -->
        </div>
    </footer>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>

</html>