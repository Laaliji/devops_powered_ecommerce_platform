import React from 'react';
import MainLayout from '@/Layouts/MainLayout';

export default function Dashboard() {
    return (
        <MainLayout>
            <div className="grid grid-cols-4 gap-4">
                <div className="bg-blue-500 text-white p-4 rounded">
                    <h3 className="text-lg font-bold">Products</h3>
                    <p className="text-3xl">150</p>
                </div>
                <div className="bg-green-500 text-white p-4 rounded">
                    <h3 className="text-lg font-bold">Orders</h3>
                    <p className="text-3xl">45</p>
                </div>
                <div className="bg-yellow-500 text-white p-4 rounded">
                    <h3 className="text-lg font-bold">Invoices</h3>
                    <p className="text-3xl">38</p>
                </div>
                <div className="bg-red-500 text-white p-4 rounded">
                    <h3 className="text-lg font-bold">Revenue</h3>
                    <p className="text-3xl">$5.2K</p>
                </div>
            </div>
        </MainLayout>
    );
}
