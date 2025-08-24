<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Live Terminal</title>

    <!-- Xterm.js Core: The terminal emulator -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>

    <!-- Xterm.js Addons: For better functionality -->
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-web-links@0.9.0/lib/xterm-addon-web-links.js"></script>

    <style>
        /* Basic styling to make the terminal fill the entire page */
        html, body {
            height: 100%;
            margin: 0;
            background-color: #282a36; /* Dracula theme background */
        }
        #terminal-container {
            width: 100vw;
            height: 100vh;
            padding: 10px;
            box-sizing: border-box;
        }
        #terminal {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>
    <div id="terminal-container">
        <div id="terminal"></div>
    </div>

<script>
    // =================================================================
    //  1. INITIALIZATION & CONFIGURATION
    // =================================================================
    const term = new Terminal({
        cursorBlink: true,
        fontFamily: 'Menlo, "DejaVu Sans Mono", Consolas, "Lucida Console", monospace',
        fontSize: 14,
        // This theme object makes it look like a professional terminal
        theme: {
            background: '#282a36',
            foreground: '#f8f8f2',
            cursor: '#f8f8f2',
            selectionBackground: '#44475a',
            black: '#000000',
            red: '#ff5555',
            green: '#50fa7b',
            yellow: '#f1fa8c',
            blue: '#bd93f9',
            magenta: '#ff79c6',
            cyan: '#8be9fd',
            white: '#bfbfbf',
            brightBlack: '#4d4d4d',
            brightRed: '#ff6e67',
            brightGreen: '#5af78e',
            brightYellow: '#f4f99d',
            brightBlue: '#caa9fa',
            brightMagenta: '#ff92d0',
            brightCyan: '#9aedfe',
            brightWhite: '#e6e6e6'
        }
    });

    // Load addons
    const fitAddon = new FitAddon.FitAddon();
    term.loadAddon(fitAddon);
    term.loadAddon(new WebLinksAddon.WebLinksAddon());

    // Attach the terminal to the DOM and make it fit the container
    const terminalEl = document.getElementById('terminal');
    term.open(terminalEl);
    fitAddon.fit();
    window.addEventListener('resize', () => fitAddon.fit());


    // =================================================================
    //  2. TERMINAL STATE & LOGIC
    // =================================================================
    let command = '';
    let commandHistory = [];
    let historyIndex = 0;
    let isProcessing = false;

    // Define the prompt appearance
    const user = 'laravel';
    const host = 'localhost';
    const path = '~';

    function prompt() {
        command = '';
        term.write(`\r\n\x1b[1;32m${user}@${host}\x1b[0m:\x1b[1;34m${path}\x1b[0m$ `);
    }

    // Welcome message
    term.writeln('🚀 Welcome to Laravel Live Terminal!');
    term.writeln('Type `list` or `help` to see available commands.');
    prompt();
    term.focus(); // Focus the terminal on load

    // =================================================================
    //  3. COMMAND EXECUTION (Communication with Backend)
    // =================================================================
    async function runCommand(command) {
        if (command.trim() === '') {
            prompt();
            return;
        }

        // Handle the 'clear' command on the frontend
        if (command.trim() === 'clear') {
            term.clear();
            prompt();
            if (!commandHistory.includes('clear')) commandHistory.push('clear');
            historyIndex = commandHistory.length;
            return;
        }
        
        isProcessing = true;
        term.writeln(''); // New line after the command
        term.write('⏳ Running command...');

        try {
            const response = await fetch("{{ route('terminal.run') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json",
                },
                body: JSON.stringify({ command: command.trim() })
            });

            const data = await response.json();

            // Clear the "Running command..." loading line
            term.write('\r\x1b[K');

            // Sanitize and write the output from the server
            const output = data.output.replace(/\r\n/g, '\n').replace(/\n/g, '\r\n');
            term.write(output);

        } catch (error) {
            term.write('\r\x1b[K'); // Clear loading line
            term.writeln(`\x1b[31mError: Could not connect to the server.\x1b[0m`);
            console.error(error);
        } finally {
            // Add command to history if it's not a duplicate of the last one
            if (command.trim() && commandHistory[commandHistory.length - 1] !== command.trim()) {
                commandHistory.push(command.trim());
            }
            historyIndex = commandHistory.length;
            isProcessing = false;
            prompt();
        }
    }

    // =================================================================
    //  4. KEYBOARD INPUT HANDLING
    // =================================================================
    term.onKey(({ key, domEvent }) => {
        if (isProcessing) return;

        const printable = !domEvent.altKey && !domEvent.ctrlKey && !domEvent.metaKey;

        switch (domEvent.key) {
            case 'Enter':
                runCommand(command);
                break;
            case 'Backspace':
                if (command.length > 0) {
                    term.write('\b \b');
                    command = command.slice(0, -1);
                }
                break;
            case 'ArrowUp':
                if (historyIndex > 0) {
                    historyIndex--;
                    const promptText = `\x1b[1;32m${user}@${host}\x1b[0m:\x1b[1;34m${path}\x1b[0m$ `;
                    term.write('\r\x1b[K' + promptText); // Clear line and write prompt
                    command = commandHistory[historyIndex];
                    term.write(command);
                }
                break;
            case 'ArrowDown':
                if (historyIndex < commandHistory.length - 1) {
                    historyIndex++;
                    const promptText = `\x1b[1;32m${user}@${host}\x1b[0m:\x1b[1;34m${path}\x1b[0m$ `;
                    term.write('\r\x1b[K' + promptText); // Clear line and write prompt
                    command = commandHistory[historyIndex];
                    term.write(command);
                } else {
                    historyIndex = commandHistory.length;
                    command = '';
                    const promptText = `\x1b[1;32m${user}@${host}\x1b[0m:\x1b[1;34m${path}\x1b[0m$ `;
                    term.write('\r\x1b[K' + promptText); // Clear line and write prompt
                }
                break;
            case 'c': // Handle Ctrl+C to clear the current line
                if (domEvent.ctrlKey) {
                    term.write('^C');
                    prompt();
                } else {
                     command += key;
                     term.write(key);
                }
                break;
            default:
                if (printable) {
                    command += key;
                    term.write(key);
                }
        }
    });

</script>

</body>
</html>