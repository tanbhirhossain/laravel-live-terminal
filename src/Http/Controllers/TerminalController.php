<?php

namespace Tanbhirhossain\LaravelLiveTerminal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TerminalController extends Controller
{
    public function index()
    {
        return view('terminal::index'); // Note the namespace for the view
    }

    public function run(Request $request)
    {
        $request->validate(['command' => 'required|string']);

        $command = $request->input('command');

        // Extract the base command to check against the whitelist
        $baseCommand = explode(' ', $command)[0];

        if (!in_array($baseCommand, config('terminal.allowed_commands'))) {
            return response()->json([
                'output' => "❌ Error: Command not allowed.\nOnly whitelisted commands can be executed.",
                'success' => false
            ]);
        }

        try {
            // Using PHP_BINARY constant is more reliable than a hardcoded path.
            // Ensure the web server user (e.g., www-data) has permission to execute it.
            $process = Process::fromShellCommandline(
                PHP_BINARY . ' ' . base_path('artisan') . ' ' . $command . ' --ansi'
            );

            $process->setWorkingDirectory(base_path());
            $process->setTimeout(120); // Increase timeout for long commands
            $process->run();

            if (!$process->isSuccessful()) {
                // Throw an exception to catch both stdout and stderr
                throw new ProcessFailedException($process);
            }

            return response()->json([
                'output' => $process->getOutput(),
                'success' => true
            ]);

        } catch (ProcessFailedException $exception) {
            return response()->json([
                // Return the error output for better debugging
                'output' => $exception->getProcess()->getOutput() ?: $exception->getProcess()->getErrorOutput(),
                'success' => false
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'output' => 'An unexpected server error occurred: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}