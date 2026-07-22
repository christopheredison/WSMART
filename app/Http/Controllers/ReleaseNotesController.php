<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class ReleaseNotesController extends Controller
{
    public function index()
    {
        $path = resource_path('releases');

        // Proteksi jika folder belum dibuat
        if (!File::exists($path)) {
            $releases = collect();
            return view('releases.index', compact('releases'));
        }

        // BEST PRACTICE:
        // Selalu hapus cache secara otomatis jika kita sedang di environment local.
        // Jadi saat Anda ngetik file .md, perubahannya langsung terlihat.
        if (app()->environment('local')) {
            Cache::forget('release_notes');
        }

        // Ambil dari Cache (jika di production) ATAU baca file baru lalu simpan ke Cache
        $releases = Cache::rememberForever('release_notes', function () use ($path) {
            $files = File::files($path);

            return collect($files)->map(function ($file) {
                $document = YamlFrontMatter::parseFile($file->getPathname());

                return (object) [
                    'title'   => $document->title,
                    'version' => $document->version,
                    'date'    => $document->date,
                    'body'    => $document->body(),
                ];
            })
            ->sortByDesc('date')
            ->values();
        });

        return view('releases.index', compact('releases'));
    }
}
