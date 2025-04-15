<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'report_date' => 'required|date',
            'project_code' => 'required|string',
            'location' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required',
            'work_day_type' => 'required|in:Hari Kerja,Hari Libur',
            'work_details' => 'required|array|min:1',
            'work_details.*.description' => 'required|string',
            'work_details.*.status' => 'required|in:Selesai,Dalam Proses,Tertunda,Bermasalah',
            'verifikator_id' => 'required|exists:users,id',
            'vp_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Get custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'report_date.required' => 'Tanggal laporan harus diisi.',
            'report_date.date' => 'Tanggal laporan harus berupa format tanggal yang valid.',
            'project_code.required' => 'Kode proyek harus diisi.',
            'location.required' => 'Lokasi harus diisi.',
            'start_time.required' => 'Waktu mulai harus diisi.',
            'end_time.required' => 'Waktu selesai harus diisi.',
            'work_day_type.required' => 'Jenis hari kerja harus diisi.',
            'work_day_type.in' => 'Jenis hari kerja tidak valid.',
            'work_details.required' => 'Detail pekerjaan harus diisi.',
            'work_details.array' => 'Detail pekerjaan harus berupa array.',
            'work_details.min' => 'Detail pekerjaan minimal harus ada 1.',
            'work_details.*.description.required' => 'Deskripsi pekerjaan harus diisi.',
            'work_details.*.status.required' => 'Status pekerjaan harus diisi.',
            'work_details.*.status.in' => 'Status pekerjaan tidak valid.',
            'verifikator_id.required' => 'Verifikator harus dipilih.',
            'verifikator_id.exists' => 'Verifikator tidak valid.',
            'vp_id.required' => 'Vice President harus dipilih.',
            'vp_id.exists' => 'Vice President tidak valid.',
        ];
    }
} 