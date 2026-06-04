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
        return Inertia::render('Companies/Index', ['companies' => $companies]);
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
            'tel' => 'nullable|string|max:20',
        ]);

        Company::create($validated);
        return redirect()->route('companies.index');
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