<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::where('status', '!=', 0)
            ->orderBy('status', 'ASC') // Ưu tiên hiện tin chưa đọc (status=1) lên trước
            ->orderBy('created_at', 'DESC')
            ->get();
        return response()->json(['success' => true, 'data' => $contacts], 200);
    }
    public function show($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        return response()->json(['success' => true, 'data' => $contact], 200);
    }
    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $contact->status = $request->status ?? $contact->status;
        $contact->reply_id = 1; // Giả sử admin id 1 đã trả lời
        $contact->updated_at = now();
        $contact->updated_by = 1;
        $contact->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công', 'data' => $contact], 200);
    }
    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        }
        $contact->delete();
        return response()->json(['success' => true, 'message' => 'Xóa liên hệ thành công'], 200);
    }
}