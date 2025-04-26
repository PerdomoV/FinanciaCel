<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Crédito</title>
    <!-- Include Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
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
                    option.textContent = `${phone.brand} ${phone.model} - $${phone.price}`;
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

                // Show success message
                document.getElementById('successMessage').textContent = data.message;
                document.getElementById('successAlert').classList.remove('hidden');
                
                // Reset form
                e.target.reset();

            } catch (error) {
                // Show error message
                document.getElementById('errorMessage').textContent = error.message;
                document.getElementById('errorAlert').classList.remove('hidden');
            }
        });
    </script>
</body>
</html> 