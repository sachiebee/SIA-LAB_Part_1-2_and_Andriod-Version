package com.example.memeverseapp.ui.fragments;

import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import androidx.fragment.app.Fragment;
import com.example.memeverseapp.R;
import com.example.memeverseapp.models.LoginResponse;
import com.example.memeverseapp.network.ApiService;
import com.example.memeverseapp.network.RetrofitClient;
import com.example.memeverseapp.services.NotificationPollingService;
import com.example.memeverseapp.ui.MainActivity;
import com.example.memeverseapp.utils.PreferencesManager;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginFragment extends Fragment {
    private EditText etLogin, etPassword;
    private Button btnLogin;
    private ApiService apiService;
    private PreferencesManager prefManager;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.fragment_login, container, false);

        etLogin = view.findViewById(R.id.etLogin);
        etPassword = view.findViewById(R.id.etPassword);
        btnLogin = view.findViewById(R.id.btnLogin);

        apiService = RetrofitClient.getClient().create(ApiService.class);
        prefManager = new PreferencesManager(requireContext());

        btnLogin.setOnClickListener(v -> {
            String login = etLogin.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            if (login.isEmpty() || password.isEmpty()) {
                Toast.makeText(getContext(), "Please fill all fields", Toast.LENGTH_SHORT).show();
                return;
            }
            performLogin(login, password);
        });

        return view;
    }

    private void performLogin(String login, String password) {
        btnLogin.setEnabled(false);
        btnLogin.setText("Logging in...");

        apiService.login(login, password).enqueue(new Callback<LoginResponse>() {
            @Override
            public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                btnLogin.setEnabled(true);
                btnLogin.setText("Login");

                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    // Save user data
                    prefManager.setUserId(response.body().getUser_id());
                    prefManager.setUsername(response.body().getUsername());
                    prefManager.setLoggedIn(true);

                    Toast.makeText(getContext(), "Welcome " + response.body().getUsername() + "!", Toast.LENGTH_SHORT).show();

                    // Start notification service
                    Intent serviceIntent = new Intent(getContext(), NotificationPollingService.class);
                    requireContext().startService(serviceIntent);

                    // Clear back stack and start MainActivity
                    Intent intent = new Intent(getActivity(), MainActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    requireActivity().finishAffinity();

                } else {
                    String error = response.body() != null ? response.body().getError() : "Login failed";
                    Toast.makeText(getContext(), error, Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<LoginResponse> call, Throwable t) {
                btnLogin.setEnabled(true);
                btnLogin.setText("Login");
                Toast.makeText(getContext(), "Network error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }
}