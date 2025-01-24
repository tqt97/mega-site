import React from 'react';

interface ModalProps {
    show: boolean;
    onClose: () => void;
    title?: string;
    children: React.ReactNode;
}

export default function Modal({ show, onClose, title, children }: ModalProps) {
    if (!show) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-50">
            <div className="w-full max-w-3xl rounded-lg bg-white p-6 shadow-lg">
                <div className="mb-4 flex items-center justify-between">
                    {title && (
                        <h3 className="text-lg font-semibold">{title}</h3>
                    )}

                    <button
                        className="text-gray-600 hover:text-gray-800"
                        onClick={onClose}
                    >
                        <span className="text-2xl">&times;</span>
                    </button>
                </div>
                <div className="mb-4">{children}</div>
                {/* <div className="flex justify-end">
                    <button
                        className="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-700"
                        onClick={onClose}
                    >
                        Close
                    </button>
                </div> */}
            </div>
        </div>
    );
}
