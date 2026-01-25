<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attribute;
class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::orderBy('id', 'ASC')->get();
        return response()->json(['success' => true, 'data' => $attributes], 200);
    }
    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);
        $attr = new Attribute();
        $attr->name = $request->name;
        $attr->save();
        return response()->json(['success' => true, 'message' => 'Thêm thành công', 'data' => $attr], 201);
    }
    public function show($id)
    {
        $attr = Attribute::find($id);
        if (!$attr) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        return response()->json(['success' => true, 'data' => $attr], 200);
    }
    public function update(Request $request, $id)
    {
        $attr = Attribute::find($id);
        if (!$attr) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $attr->name = $request->name;
        $attr->save();
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công'], 200);
    }
    public function destroy($id)
    {
        $attr = Attribute::find($id);
        if (!$attr) return response()->json(['success' => false, 'message' => 'Không tìm thấy'], 404);
        $attr->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
    }
}