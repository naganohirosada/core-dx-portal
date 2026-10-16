<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class CompanyController extends Controller
{
    /** 一覧画面 */
    public function index()
    {
        $companies = Company::withCount('projects')->latest()->get();
        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'importResult' => session('import_result'),
        ]);
    }

    /** 💡 新規登録画面の表示 */
    public function create()
    {
        return Inertia::render('Companies/Create');
    }

    public function show(\App\Models\Company $company)
    {
        // この顧客に紐づく案件一覧(projects)を一緒にロード
        $company->load('projects');

        return \Inertia\Inertia::render('Companies/Show', [
            'company' => $company
        ]);
    }

    /** 新規登録処理 */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:8',
            'address' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:20',
            'tel' => 'nullable|string|max:20',
        ]);

        Company::create($validated);
        return redirect()->route('companies.index');
    }

    /**
     * 💡 顧客のCSV一括取込
     * 1行目は見出し（企業名,郵便番号,住所,電話番号,インボイス番号）。Excel保存のShift_JISにも対応する。
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // BOM除去
        if (! mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'SJIS-win');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        array_shift($lines); // 見出し行

        $imported = 0;
        $errors = [];
        foreach ($lines as $index => $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = array_pad(str_getcsv($line), 5, null);
            $data = [
                'name' => trim((string) $cols[0]),
                'postal_code' => $cols[1] !== null && trim($cols[1]) !== '' ? trim($cols[1]) : null,
                'address' => $cols[2] !== null && trim($cols[2]) !== '' ? trim($cols[2]) : null,
                'tel' => $cols[3] !== null && trim($cols[3]) !== '' ? trim($cols[3]) : null,
                'registration_number' => $cols[4] !== null && trim($cols[4]) !== '' ? trim($cols[4]) : null,
            ];

            $validator = validator($data, [
                'name' => 'required|string|max:255',
                'postal_code' => 'nullable|string|max:8',
                'address' => 'nullable|string|max:255',
                'tel' => 'nullable|string|max:20',
                'registration_number' => 'nullable|string|max:20',
            ]);
            if ($validator->fails()) {
                $errors[] = ($index + 2) . '行目: ' . $validator->errors()->first();
                continue;
            }

            Company::create($data);
            $imported++;
        }

        return redirect()->route('companies.index')->with('import_result', [
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }

    /** 💡 編集画面の表示 */
    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', ['company' => $company]);
    }

    /** 💡 更新処理 */
    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:8',
            'address' => 'nullable|string|max:255',
            'tel' => 'nullable|string|max:20',
            'registration_number' => 'nullable|string|max:20',
            'status' => 'required|string|in:active,inactive',
        ]);

        $company->update($validated);
        return redirect()->route('companies.index');
    }

    /**
     * 💡 郵便番号検索プロキシ（ブラウザのネットワークエラー対策）
     */
    public function searchPostalCode($code)
    {
        $cleanCode = str_replace('-', '', $code);

        // サーバーサイドから安全に外部APIを叩く（CORSの影響を受けない）
        $response = Http::get("https://zipcloud.ibsnet.co.jp/api/search?zipcode={$cleanCode}");

        // APIのレスポンスをそのままJSONでVueに返す
        return response()->json($response->json());
    }
}