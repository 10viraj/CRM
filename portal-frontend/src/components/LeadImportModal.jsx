import React, { useState } from 'react';
import { api } from '../services/api';

export default function LeadImportModal({ isOpen, onClose, onSuccess }) {
  const [csvText, setCsvText] = useState('');
  const [parsedRows, setParsedRows] = useState([]);
  const [importing, setImporting] = useState(false);
  const [progress, setProgress] = useState(0);
  const [error, setError] = useState('');

  if (!isOpen) return null;

  const parseCsvContent = (text) => {
    try {
      const lines = text.trim().split('\n').filter(line => line.trim().length > 0);
      if (lines.length < 2) {
        setError('CSV must contain a header row and at least one data row.');
        setParsedRows([]);
        return;
      }

      const headers = lines[0].split(',').map(h => h.trim().replace(/^["']|["']$/g, '').toLowerCase());
      const rows = [];

      for (let i = 1; i < lines.length; i++) {
        const values = lines[i].split(',').map(v => v.trim().replace(/^["']|["']$/g, ''));
        if (values.length >= 2) {
          const rowObj = {};
          headers.forEach((header, idx) => {
            rowObj[header] = values[idx] || '';
          });

          // Normalize fields
          const leadItem = {
            first_name: rowObj['first_name'] || rowObj['first name'] || rowObj['name']?.split(' ')[0] || `Lead${i}`,
            last_name: rowObj['last_name'] || rowObj['last name'] || rowObj['name']?.split(' ').slice(1).join(' ') || 'Prospect',
            email: rowObj['email'] || `lead${Date.now()}_${i}@imported.com`,
            phone: rowObj['phone'] || rowObj['mobile'] || '',
            company: rowObj['company'] || rowObj['company_name'] || rowObj['organization'] || '',
            score: Number(rowObj['score']) || 50,
            notes: rowObj['notes'] || 'Imported via CSV',
          };
          rows.push(leadItem);
        }
      }

      setParsedRows(rows);
      setError('');
    } catch (err) {
      setError('Failed to parse CSV. Please check formatting.');
      setParsedRows([]);
    }
  };

  const handleFileUpload = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (evt) => {
      const content = evt.target.result;
      setCsvText(content);
      parseCsvContent(content);
    };
    reader.readAsText(file);
  };

  const handleTextChange = (e) => {
    const text = e.target.value;
    setCsvText(text);
    parseCsvContent(text);
  };

  const handleImportSubmit = async () => {
    if (parsedRows.length === 0) return;

    setImporting(true);
    setProgress(0);
    setError('');

    let successCount = 0;
    const total = parsedRows.length;

    for (let i = 0; i < total; i++) {
      try {
        await api.leads.create(parsedRows[i]);
        successCount++;
      } catch (err) {
        console.error('Failed to import row', parsedRows[i], err);
      }
      setProgress(Math.round(((i + 1) / total) * 100));
    }

    setImporting(false);
    onSuccess(successCount);
    onClose();
  };

  const sampleCsv = `first_name,last_name,email,company,phone,score
Elon,Musk,elon@spacex.test,SpaceX,+1 555-0100,95
Satya,Nadella,satya@microsoft.test,Microsoft,+1 555-0101,90
Tim,Cook,tim@apple.test,Apple,+1 555-0102,88`;

  return (
    <div className="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden border border-slate-100 transition-all">
        {/* Header */}
        <div className="px-6 py-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex justify-between items-center">
          <div>
            <h3 className="text-lg font-bold">Import Leads from CSV</h3>
            <p className="text-xs text-slate-300 mt-0.5">Upload a CSV file or paste formatted rows directly</p>
          </div>
          <button
            onClick={onClose}
            type="button"
            className="text-slate-400 hover:text-white rounded-lg p-1.5 transition hover:bg-slate-700/50"
          >
            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Content */}
        <div className="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
          {error && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
              {error}
            </div>
          )}

          {/* File Upload Zone */}
          <div className="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:border-indigo-400 transition bg-slate-50/50">
            <svg className="w-10 h-10 text-slate-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p className="text-sm font-semibold text-slate-700">Choose CSV file or drag and drop</p>
            <p className="text-xs text-slate-400 mt-1">.csv or comma-separated text</p>
            <input
              type="file"
              accept=".csv,text/csv"
              onChange={handleFileUpload}
              className="mt-3 text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
            />
          </div>

          {/* Direct CSV Input */}
          <div>
            <div className="flex justify-between items-center mb-1">
              <label className="text-xs font-semibold text-slate-700">Or Paste CSV Content</label>
              <button
                type="button"
                onClick={() => { setCsvText(sampleCsv); parseCsvContent(sampleCsv); }}
                className="text-xs text-indigo-600 hover:underline font-semibold"
              >
                Load Sample Data
              </button>
            </div>
            <textarea
              rows="4"
              value={csvText}
              onChange={handleTextChange}
              placeholder="first_name,last_name,email,company,phone,score"
              className="w-full px-3.5 py-2 font-mono text-xs border border-slate-200 rounded-xl bg-slate-50/50 focus:bg-white outline-none focus:ring-2 focus:ring-indigo-500 transition resize-none"
            />
          </div>

          {/* Parsed Preview Table */}
          {parsedRows.length > 0 && (
            <div>
              <div className="flex justify-between items-center mb-2">
                <span className="text-xs font-bold text-slate-800">
                  Preview ({parsedRows.length} Leads Ready)
                </span>
                <span className="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">
                  Ready to Import
                </span>
              </div>
              <div className="border border-slate-200 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                <table className="w-full text-left text-xs">
                  <thead className="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 sticky top-0">
                    <tr>
                      <th className="px-3 py-2">Name</th>
                      <th className="px-3 py-2">Email</th>
                      <th className="px-3 py-2">Company</th>
                      <th className="px-3 py-2">Score</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-100">
                    {parsedRows.slice(0, 10).map((row, idx) => (
                      <tr key={idx} className="hover:bg-slate-50">
                        <td className="px-3 py-2 font-medium text-slate-900">{row.first_name} {row.last_name}</td>
                        <td className="px-3 py-2 text-slate-500">{row.email}</td>
                        <td className="px-3 py-2 text-slate-700">{row.company || '-'}</td>
                        <td className="px-3 py-2 font-semibold text-indigo-600">{row.score}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              {parsedRows.length > 10 && (
                <p className="text-[11px] text-slate-400 mt-1 text-right">
                  + {parsedRows.length - 10} more rows...
                </p>
              )}
            </div>
          )}

          {/* Progress Bar */}
          {importing && (
            <div className="space-y-1">
              <div className="flex justify-between text-xs font-semibold text-slate-600">
                <span>Importing records to database...</span>
                <span>{progress}%</span>
              </div>
              <div className="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div
                  className="bg-indigo-600 h-full transition-all duration-200"
                  style={{ width: `${progress}%` }}
                />
              </div>
            </div>
          )}

          {/* Footer */}
          <div className="pt-4 border-t border-slate-100 flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              disabled={importing}
              className="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition"
            >
              Cancel
            </button>
            <button
              type="button"
              disabled={parsedRows.length === 0 || importing}
              onClick={handleImportSubmit}
              className="px-6 py-2.5 text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md shadow-indigo-200 transition disabled:opacity-50 flex items-center space-x-2"
            >
              {importing ? (
                <>
                  <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                  </svg>
                  <span>Importing...</span>
                </>
              ) : (
                <span>Import {parsedRows.length} Leads</span>
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
