        .stats-card {
            text-align: center;
            padding: 1.5rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            border: none;
            color: white;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            z-index: 1;
        }

        .stats-card-primary {
            background: linear-gradient(135deg, #dab98b, #c9a876);
            box-shadow: 0 4px 15px rgba(218, 185, 139, 0.3);
        }

        .stats-card-secondary {
            background: linear-gradient(135deg, #d4a574, #c19660);
            box-shadow: 0 4px 15px rgba(212, 165, 116, 0.3);
        }

        .stats-card-success {
            background: linear-gradient(135deg, #b8956b, #a6855d);
            box-shadow: 0 4px 15px rgba(184, 149, 107, 0.3);
        }

        .stats-card-warning {
            background: linear-gradient(135deg, #e6c794, #d9b885);
            box-shadow: 0 4px 15px rgba(230, 199, 148, 0.3);
            color: #5a4a3a;
        }

        .stats-card-info {
            background: linear-gradient(135deg, #c7a373, #b89465);
            box-shadow: 0 4px 15px rgba(199, 163, 115, 0.3);
        }

        .stats-card-danger {
            background: linear-gradient(135deg, #d2a86e, #c19660);
            box-shadow: 0 4px 15px rgba(210, 168, 110, 0.3);
        }

        .stats-card-muted {
            background: linear-gradient(135deg, #a8916a, #96815c);
            box-shadow: 0 4px 15px rgba(168, 145, 106, 0.3);
        }

        .stats-icon-container {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            padding: 1rem;
            margin: 0 auto 1rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .stats-icon {
            font-size: 1.8rem;
            color: white;
        }

        .stats-content {
            position: relative;
            z-index: 2;
        }

        .stats-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
            color: white;
            line-height: 1.2;
        }

        .stats-label {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            color: white;
        }

        .stats-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }

        .stats-trend {
            margin-top: 0.5rem;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            background: rgba(255, 255, 255, 0.15);
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            margin-left: auto;
            margin-right: auto;
            width: fit-content;
        }

        .stats-trend i {
            font-size: 0.9rem;
        }

        .stats-trend small {
            font-weight: 500;
            font-size: 0.8rem;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .stats-card-warning .stats-trend small,
        .stats-card-warning .stats-subtitle,
        .stats-card-warning .stats-label,
        .stats-card-warning .stats-value {
            color: #5a4a3a;
        }

        .stats-card-warning .stats-icon {
            color: #5a4a3a;
        }

        .action-log-container {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .action-log-body {
            flex: 1;
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .action-log-body::-webkit-scrollbar {
            width: 8px;
        }

        .action-log-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .action-log-body::-webkit-scrollbar-thumb {
            background: #dab98b;
            border-radius: 10px;
        }

        .action-log-body::-webkit-scrollbar-thumb:hover {
            background: #c9a876;
        }

        // Get general statistics
        $statsQuery = "SELECT 
                       (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE is_approved = 1 AND status = 'active') as approved_kitchens,
                       (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'blocked') as blocked_kitchens,
                       (SELECT COUNT(*) FROM cloud_kitchen_owner WHERE status = 'suspended') as suspended_kitchens,
                       (SELECT COUNT(*) FROM orders) as total_orders,
                       (SELECT COUNT(*) FROM meals) as total_meals,
                       (SELECT SUM(total_price) FROM orders) as total_revenue,
                       (SELECT SUM(total_price) * 0.15 FROM orders) as total_earnings"; 