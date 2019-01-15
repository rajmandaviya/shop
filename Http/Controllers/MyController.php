<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserRepository;

use Modules\Order\Entities\Order;
use Modules\Core\Http\Controllers\BasePublicController;
use Modules\Order\Repositories\OrderRepository;

/**
 * MyController
 */
class MyController extends BasePublicController
{
    /**
     * @var Authentication
     */
    protected $auth;

    /**
     * @var OrderRepository
     */
    protected $order;

    /**
     * @var UserRepository
     */
    protected $user;

    /**
     * @inheritDoc
     */
    public function __construct(Authentication $auth, OrderRepository $order, UserRepository $user)
    {
        parent::__construct();

        $this->auth = $auth;
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * 대시보드
     * Dashboard
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        return view('shop.my.dashboard', compact('user'));
    }

    /**
     * 주문목록
     * Order Index Table
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function orders(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        $orders = Order::scopeByUser($user->id)->latest()->paginate(10);
        return view('shop.my.order.index', compact('user', 'orders'));
    }

    /**
     * 주문보기
     * Order View
     *
     * @param  Order $order
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function orderView(Order $order, Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');

        return view('shop.my.order.view', compact('user', 'order'));
    }

    /**
     * 주문 거래명세어
     * Order Form
     *
     * @param  Order $order
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function orderForm(Order $order, Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');

        $items = collect();
        $order->items->map(function($item) use ($items) {
            $product_name = $item->product->name;

            if($items->count() > 0) {
              $items->map(function($item2) use ($items, $item, $product_name) {
                if($item2[$product_name]) {
                  $item2[$product_name]->quantity = $item2[$product_name]->quantity + $item->quantity;
                  $item2[$product_name]->total = $item2[$product_name]->total + $item->total;
                }
                else {
                  $items->push([$product_name => $item]);
                }
              });
            }
            else {
              $items->push([$product_name => $item]);
            }
        });

        return view('shop.my.order.form', compact('user', 'order', 'items'));
    }

    /**
     * 프로필
     * Profile
     *
     * @param  Request $request
     * @return \Illuminate\View\View
     */
    public function profile(Request $request)
    {
        $user = $this->auth->user();
        $user->load('profile');
        return view('shop.my.profile', compact('user'));
    }

    /**
    * 프로필 저장
    * Profile Store
    *
    * @param  Request $request
    * @return \Illuminate\View\View
    */
   public function profileStore(Request $request)
   {
       $data = $request->all();
       $user = $this->auth->user();

       $this->user->update($user, $request->all());

       return redirect()->route('shop.my.profile')->with('success', trans('shop::theme.profile saved'));
   }

}
