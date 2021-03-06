<?php

/**
 * Created by PhpStorm.
 * User: nimeshjayasankha
 * Date: 12/25/20
 * Time: 8:09 PM
 */

namespace App\Http\Controllers;

use App\Category;
use App\Employee;
use App\Http\Requests\BrandUpdateRequest;
use App\Http\Requests\CategoryRequest;
use App\MasterConstruction;
use App\Payment;
use App\PaymentConstruction;
use App\Plan;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ConstructionController extends Controller
{
    public function makeConstIndex(Request $request)
    {

        $plans = Plan::where('status', 1)->get();
        $products = Product::where('status', 1)->get();

        return view('make_a_construction.make-a-construction', ['title' => 'Make a Construction', 'plans' => $plans, 'products' => $products]);
    }

    public function pendingConstructionsIndex(Request $request)
    {

        $constructionId = $request['constructionId'];
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $customer = $request['customer'];

        $customerNames = User::where('status', 1)->where('user_role_iduser_role',3)->get();

        $query = MasterConstruction::query();

        if ($constructionId) {
            $query = $query->where('idmaster_construction', $constructionId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = date('Y-m-d', strtotime($request['startDate']));
            $endDate = date('Y-m-d', strtotime($request['endDate']));

            $query = $query->whereBetween('created_date', [$startDate, $endDate]);
        }
        if (!empty($customer)) {

            $query = $query->where('customer', $customer);
        }

        $constructionDetails = $query->where('status', 1)->latest()->paginate(10);

        $constructionDetails->appends(array(
            'startDate' => $request['startDate'],
            'endDate' => $request['endDate'],
            'constructionId' => $request['constructionId'],
        ));
        return view('pending_constructions.pending-constructions', ['title' => 'Pending Constructions', 'constructionDetails' => $constructionDetails, 'customerNames' => $customerNames]);
    }

    public function onGoingIndex(Request $request)
    {

        $constructionId = $request['constructionId'];
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $customer = $request['customer'];

        $customerNames = User::where('status', 1)->where('user_role_iduser_role',3)->get();


        $query = MasterConstruction::query();

        if ($constructionId) {
            $query = $query->where('idmaster_construction', $constructionId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = date('Y-m-d', strtotime($request['startDate']));
            $endDate = date('Y-m-d', strtotime($request['endDate']));

            $query = $query->whereBetween('created_date', [$startDate, $endDate]);
        }
        if (!empty($customer)) {

            $query = $query->where('customer', $customer);
        }

        $constructionDetails = $query->where('status', 2)->latest()->paginate(10);

        $constructionDetails->appends(array(
            'startDate' => $request['startDate'],
            'endDate' => $request['endDate'],
            'constructionId' => $request['constructionId'],
        ));

        $employees = User::where('status', 1)->where('user_role_iduser_role', '=', '2')->get();
        return view('on_going_constructions.on-going-constructions', ['title' => 'On Going Constructions', 'constructionDetails' => $constructionDetails, 'customerNames' => $customerNames, 'employees' => $employees]);
    }

    public function completedConstructionsIndex(Request $request)
    {

        $constructionId = $request['constructionId'];
        $startDate = $request['startDate'];
        $endDate = $request['endDate'];
        $customer = $request['customer'];

        $customerNames = User::where('status', 1)->where('user_role_iduser_role',3)->get();

        $query = MasterConstruction::query();

        if ($constructionId) {
            $query = $query->where('idmaster_construction', $constructionId);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = date('Y-m-d', strtotime($request['startDate']));
            $endDate = date('Y-m-d', strtotime($request['endDate']));

            $query = $query->whereBetween('created_date', [$startDate, $endDate]);
        }
        if (!empty($customer)) {

            $query = $query->where('customer', $customer);
        }

        $constructionDetails = $query->where('status', 3)->latest()->paginate(10);

        $constructionDetails->appends(array(
            'startDate' => $request['startDate'],
            'endDate' => $request['endDate'],
            'constructionId' => $request['constructionId'],
        ));

        $employees = User::where('status', 1)->where('user_role_iduser_role', '=', '2')->get();
        return view('completed_constructions.completed-constructions', ['title' => 'On Going Constructions', 'constructionDetails' => $constructionDetails, 'customerNames' => $customerNames, 'employees' => $employees]);
    }

    public function viewPlanImg(Request $request)
    {

        $planID = $request['planID'];
        $getPlanImg = Plan::find($planID);

        return $getPlanImg;
    }

    public function approvedConstruction(Request $request)
    {

        $id = $request['id'];

        $changeStatus = MasterConstruction::find($id);
        if ($changeStatus != null) {
            $changeStatus->status = 2;
            $changeStatus->save();

            return response()->json(['success' => 'Construction approved successfully.']);
        }
    }

    public function saveConEmployee(Request $request)
    {
        $validator = \Validator::make($request->all(), [

            'employee' => 'required',
        ], [
            'employee.required' => 'Employee should be provided!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $save = new Employee();
        $save->master_construction_id = $request['hiddenConId'];
        $save->user_iduser = $request['employee'];
        $save->save();

        return response()->json(['success' => 'Employee saved successfully.']);
    }

    public function viewEmployees(Request $request)
    {

        $id = $request['id'];

        $viewAllemplyees = Employee::where('master_construction_id', $id)->get();
        $tableData = "";
        if (count($viewAllemplyees) != 0) {
            foreach ($viewAllemplyees as $viewAllemplyee) {


                $tableData .= "<tr>";
                $tableData .= "<td>" . $viewAllemplyee->User->first_name . ' ' . $viewAllemplyee->User->last_name . "</td>";
                $tableData .= "<td>";
                $tableData .= " <p>";
                $tableData .= "<button type='button' class='btn btn-sm btn-danger  waves-effect waves-light'
                    onclick='deleteEmployee($viewAllemplyee->idEmployee,$viewAllemplyee->master_construction_id)'>";
                $tableData .= "<i class='fa fa-trash'></i>";
                $tableData .= "</button>";
                $tableData .= " </p>";
                $tableData .= " </td>";
                $tableData .= "</tr>";
            }
        } else {
            $tableData = "<tr><td colspan='8' style='text-align: center'><b>Sorry No Results Found.</b></td></tr>";
        }

        return $tableData;
    }

    public function deleteEmployee(Request $request)
    {

        $id = $request['id'];
        $deleteEmployee = Employee::find($id);
        if ($deleteEmployee != null) {
            $deleteEmployee->delete();
        }

        return response()->json(['success' => 'Employee deleted successfully']);
    }

    public function savePayment(Request $request)
    {

        $validator = \Validator::make($request->all(), [

            'payment' => 'required|not_in:0',
        ], [
            'payment.required' => 'Payment should be provided!',
            'payment.not_in' => 'Payment should be more than 0!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $save = new PaymentConstruction();
        $save->status = 1;
        $save->master_construction_id = $request['hiddenConId'];
        $save->paid_amount = $request['payment'];
        $save->user_iduser = Auth::user()->iduser;
        $save->save();

        $constructionPayment = MasterConstruction::find($request['hiddenConId']);
        $constructionPayment->paid_amount += $request['payment'];
        $constructionPayment->save();

        $constructionPayment = MasterConstruction::find($request['hiddenConId']);
        $dueAmount = $constructionPayment->total - $constructionPayment->paid_amount;
        $constructionPayment->due_amount = $dueAmount;
        $constructionPayment->save();

        return response()->json(['success' => 'Payment saved successfully.']);
    }

    public function paymentHistory(Request $request)
    {

        $paymentDetails = PaymentConstruction::where('master_construction_id', $request['constructionId'])->orderBy('idpayment', 'DESC')->get();

        $tableData = "";
        $number = 0;
        foreach ($paymentDetails as $paymentDetail) {

            if ($number % 2 == 0) {
                $tableData .= "<section id='cd-timeline' class='cd-container'>";
                $tableData .= "<div class='cd-timeline-block'>";
                $tableData .= "<div class='cd-timeline-img bg-success'>";
                $tableData .= "<i class='mdi mdi-adjust'></i>";
                $tableData .= "</div> ";
                $tableData .= "<div class='cd-timeline-content'>";
                $tableData .= "<h3>" . number_format($paymentDetail->paid_amount, 2) . "</h3>";
                $tableData .= "<span class='cd-date'>" . $paymentDetail->created_at->toDateString() . "</span>";
                $tableData .= "</div>";
                $tableData .= "</div>";
            } else {
                $tableData .= "<div class='cd-timeline-block'>";
                $tableData .= "<div class='cd-timeline-img bg-danger'>";
                $tableData .= "<i class='mdi mdi-adjust'></i>";
                $tableData .= "</div>";
                $tableData .= "<div class='cd-timeline-content'>";
                $tableData .= "<h3>" . number_format($paymentDetail->paid_amount, 2) . "</h3>";
                $tableData .= "<span class='cd-date'>" . $paymentDetail->created_at->toDateString() . "</span>";
                $tableData .= "</div>";
                $tableData .= "</div>";
                $tableData .= "</section>";
            }
            $number++;
        }

        return $tableData;
    }

    public function completedConstruction(Request $request){

        $updateConstruction=MasterConstruction::find($request['id']);
        $updateConstruction->status=3;
        $updateConstruction->update();

        return response()->json(['success'=>'Contruction completed successfully']);
    }
}
