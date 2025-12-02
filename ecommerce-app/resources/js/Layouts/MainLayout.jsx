import React from 'react';

export default function MainLayout({ children }) {
    return (
        <div>
            <nav className="bg-white border-b border-gray-200">
                <div className="px-4 py-3">
                    <h1 className="text-2xl font-bold">E-Commerce</h1>
                </div>
            </nav>
            <main className="container mx-auto p-4">
                {children}
            </main>
        </div>
    );
}
