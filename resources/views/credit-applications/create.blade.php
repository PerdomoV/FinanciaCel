<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Crédito</title>
    <!-- Include Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Include SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-6 text-center">Nueva Solicitud de Crédito</h2>
            
            <!-- Error Alert -->
            <div id="errorAlert" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span id="errorMessage" class="block sm:inline"></span>
            </div>

            <!-- Success Alert -->
            <div id="successAlert" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span id="successMessage" class="block sm:inline"></span>
            </div>

            <form id="creditApplicationForm" class="space-y-4">
                <div>
                    <label for="client_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                    <select id="client_id" name="client_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione un cliente</option>
                    </select>
                </div>

                <div>
                    <label for="phone_id" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <select id="phone_id" name="phone_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Seleccione un teléfono</option>
                    </select>
                </div>

                <div>
                    <label for="term" class="block text-sm font-medium text-gray-700">Plazo (meses)</label>
                    <input type="number" id="term" name="term" min="1" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <div>
                    <label for="monthly_interest_rate" class="block text-sm font-medium text-gray-700">Tasa de Interés Mensual (%)</label>
                    <input type="number" id="monthly_interest_rate" name="monthly_interest_rate" min="0" step="0.01" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>

    <script>
        // Function to fetch and populate clients
        async function fetchClients() {
            try {
                const response = await fetch('/api/clients', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al obtener los clientes');
                }

                const data = await response.json();
                const selectElement = document.getElementById('client_id');

                data.data.forEach(client => {
                    const option = document.createElement('option');
                    option.value = client.id;
                    option.textContent = `${client.name} - CC: ${client.cc}`;
                    selectElement.appendChild(option);
                });
            } catch (error) {
                document.getElementById('errorMessage').textContent = error.message;
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        }

        // Function to fetch and populate phones
        async function fetchPhones() {
            try {
                const response = await fetch('/api/phones', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Error al obtener los teléfonos');
                }

                const data = await response.json();
                const selectElement = document.getElementById('phone_id');

                data.data.forEach(phone => {
                    const option = document.createElement('option');
                    option.value = phone.id;
                    option.textContent = `${phone.model} - $${phone.price}`;
                    selectElement.appendChild(option);
                });
            } catch (error) {
                document.getElementById('errorMessage').textContent = error.message;
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        }

        // Fetch clients and phones when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            fetchClients();
            fetchPhones();
        });

        document.getElementById('creditApplicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Hide any existing alerts
            document.getElementById('errorAlert').classList.add('hidden');
            document.getElementById('successAlert').classList.add('hidden');

            // Get form data
            const formData = {
                client_id: parseInt(document.getElementById('client_id').value),
                phone_id: parseInt(document.getElementById('phone_id').value),
                term: parseInt(document.getElementById('term').value),
                monthly_interest_rate: parseFloat(document.getElementById('monthly_interest_rate').value)
            };

            try {
                const response = await fetch('/api/credits/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Error al procesar la solicitud');
                }

                console.log(data);
                //show the dialog with the data
                showDialog(data, formData);

                // Reset form
                e.target.reset();

            } catch (error) {
                // Show error message
                document.getElementById('errorMessage').textContent = error.message;
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        });

        async function submitCreditApplication(formData) {
            try {
                const response = await fetch('/api/credits', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Error al procesar la solicitud');
                }

                return data;
            } catch (error) {
                Swal.showValidationMessage(error.message);
            }
        }

        function showDialog(data, formData){
            //show the dialog with the data
            const amortizationTableHTML = `
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 border-b text-left whitespace-nowrap min-w-[100px]">Periodo</th>
                                <th class="px-6 py-3 border-b text-right">Saldo Inicial</th>
                                <th class="px-6 py-3 border-b text-right">Valor Cuota</th>
                                <th class="px-6 py-3 border-b text-right">Valor Interés</th>
                                <th class="px-6 py-3 border-b text-right">Abono Capital</th>
                                <th class="px-6 py-3 border-b text-right">Saldo Capital</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.amortizationData.tabla_amortizacion.map(row => `
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 border-b text-left">${row.periodo}</td>
                                    <td class="px-6 py-3 border-b text-right">$${row.saldo_inicial.toFixed(2)}</td>
                                    <td class="px-6 py-3 border-b text-right">$${row.valor_cuota.toFixed(2)}</td>
                                    <td class="px-6 py-3 border-b text-right">$${row.valor_interes.toFixed(2)}</td>
                                    <td class="px-6 py-3 border-b text-right">$${row.abono_capital.toFixed(2)}</td>
                                    <td class="px-6 py-3 border-b text-right">$${row.saldo_capital.toFixed(2)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot class="bg-gray-100">
                            <tr>
                                <td colspan="2" class="px-6 py-3 border-b font-bold">Totales:</td>
                                <td class="px-6 py-3 border-b text-right font-bold">$${data.amortizationData.total_cuotas.toFixed(2)}</td>
                                <td class="px-6 py-3 border-b text-right font-bold">$${data.amortizationData.total_intereses.toFixed(2)}</td>
                                <td class="px-6 py-3 border-b text-right font-bold">$${data.amortizationData.total_abono_capital.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;

            dialogHTML = `
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-2xl font-bold mb-4">Tabla de amortización</h2>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="p-3 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600">Valor del crédito:</p>
                            <p class="text-lg font-semibold">$${data.amortizationData.valor_credito.toFixed(2)}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600">Tasa de interés:</p>
                            <p class="text-lg font-semibold">${data.amortizationData.tasa_interes}%</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded">
                            <p class="text-sm text-gray-600">Plazo:</p>
                            <p class="text-lg font-semibold">${data.amortizationData.plazo} meses</p>
                        </div>
                    </div>
                    ${amortizationTableHTML}
                </div>
            `;

            const selectedPhone = (
                document.getElementById('phone_id')
                .options[document.getElementById('phone_id').selectedIndex]
                .text
            ).split(' - ')[0];

         

            Swal.fire({
                title: 'Simulación de crédito para ' + selectedPhone,
                html: dialogHTML,
                showCloseButton: true,
                showCancelButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Confirmar solicitud',
                cancelButtonText: 'Cancelar',
                width: 1100,
                preConfirm: () => submitCreditApplication(formData)
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Solicitud Creada',
                        text: 'La solicitud de crédito ha sido creada exitosamente',
                        icon: 'success'
                    });
                }
            })
        }
    </script>
</body>
</html> 