<?php

namespace Tanbhirhossain\LaravelLiveTerminal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder; // <-- 1. IMPORT THIS CLASS
use Symfony\Component\Process\Exception\ProcessFailedException;

class TerminalController extends Controller
{
    public function index()
    {
        return view('terminal::index');
    }

    public function run(Request $request)
    {
        $request->validate(['command' => 'required|string']);

        $command = $request->input('command');
        $baseCommand = explode(' ', $command)[0];

        if (!in_array($baseCommand, config('terminal.allowed_commands'))) {
            return response()->json([
                'output' => "❌ Error: Command '$baseCommand' is not allowed.",
                'success' => false
            ]);
        }

        try {
            // --- 2. THE FIX IS HERE ---
            // First, check for a manually configured PHP path.
            $phpPath = config('terminal.php_path');

            // If not configured, find it automatically.
            if (!$phpPath) {
                $phpFinder = new PhpExecutableFinder();
                $phpPath = $phpFinder->find(false);
            }

            // If we still couldn't find it, return an error.
            if (!$phpPath) {
                 return response()->json([
                    'output' => "❌ Error: Could not find PHP executable. Please set it manually in config/terminal.php.",
                    'success' => false
                ]);
            }

            // Now, build the command with the CORRECT PHP path.
            $process = Process::fromShellCommandline(
                '"' . $phpPath . '"' . ' ' . base_path('artisan') . ' ' . $command . ' --ansi'
            );
            // --- END OF FIX ---


            $process->setWorkingDirectory(base_path());
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            return response()->json([
                'output' => $process->getOutput(),
                'success' => true
            ]);

        } catch (ProcessFailedException $exception) {
            return response()->json([
                'output' => $exception->getProcess()->getErrorOutput(),
                'success' => false
            ]);
        }
    }
}