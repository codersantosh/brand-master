/* WordPress */
import { __ } from '@wordpress/i18n';
import { useContext, useMemo } from '@wordpress/element';

/* Library */
import classNames from 'classnames';
import { map, filter, cloneDeep, forEach, isArray } from 'lodash';

/*Atrc*/
import {
	AtrcText,
	AtrcControlToggle,
	AtrcWireFrameContentSidebar,
	AtrcWireFrameHeaderContentFooter,
	AtrcPrefix,
	AtrcPanelBody,
	AtrcPanelRow,
	AtrcContent,
	AtrcTitleTemplate1,
	AtrcControlLink,
	AtrcControlSelect,
	AtrcNestedObjUpdateByKey1,
} from 'atrc';

/* Inbuilt */
import { AtrcReduxContextData } from '../../../routes';
import { DocsTitle } from '../../../components/molecules';

/*Local*/
const MainContent = () => {
	const data = useContext(AtrcReduxContextData);

	const { dbSettings, dbUpdateSetting } = data;

	let {
		hideAdminBar = { on: false },
		redirectAdminDashboard = { on: false },
		redirectLogin = { on: false },
		redirectLogout = { on: false },
		redirectLostPassword = { on: false },
		redirectRegistration = { on: false },
	} = dbSettings;

	//RESTAPI change array to object.
	const objectKeys = [
		'hideAdminBar',
		'redirectAdminDashboard',
		'redirectLogin',
		'redirectLogout',
		'redirectLostPassword',
		'redirectRegistration',
	];

	forEach(objectKeys, (key) => {
		if (isArray(dbSettings[key])) {
			dbSettings[key] = {};
		}
	});

	const userRolesOptions = useMemo(() => {
		const allUserRoles = map(brandMasterLocalize.userRoles, (label, value) => ({
			value,
			label,
		}));

		const excludeAdminUserRoles = map(
			filter(
				brandMasterLocalize.userRoles,
				(label, value) => value !== 'administrator'
			),
			(label, value) => ({
				value,
				label,
			})
		);

		return {
			all: allUserRoles,
			excludeAdmin: excludeAdminUserRoles,
		};
	}, [brandMasterLocalize.userRoles]);

	return (
		<AtrcContent>
			<AtrcPanelBody
				className={classNames(AtrcPrefix('m-0'))}
				title={__('Hide admin bar on frontend', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow>
					<AtrcControlToggle
						label={__('Hide admin bar on frontend', 'brand-master')}
						checked={hideAdminBar.on}
						onChange={() => {
							const newHideAdminBar = AtrcNestedObjUpdateByKey1({
								settings: hideAdminBar,
								key1: 'on',
								val1: !hideAdminBar.on,
							});
							dbUpdateSetting('hideAdminBar', newHideAdminBar);
						}}
					/>
				</AtrcPanelRow>
				{hideAdminBar.on ? (
					<>
						<AtrcPanelRow>
							<AtrcControlSelect
								label={__('Hide for', 'brand-master')}
								wrapProps={{
									className: 'at-flx-grw-1',
								}}
								value={hideAdminBar.hide}
								options={[
									{
										value: '',
										label: __('All users', 'brand-master'),
									},
									{
										value: 'roles',
										label: __('Selected roles', 'brand-master'),
									},
								]}
								onChange={(newVal) => {
									const newHideAdminBar = AtrcNestedObjUpdateByKey1({
										settings: hideAdminBar,
										key1: 'hide',
										val1: newVal,
									});
									dbUpdateSetting('hideAdminBar', newHideAdminBar);
								}}
							/>
						</AtrcPanelRow>
						{'roles' === hideAdminBar.hide ? (
							<AtrcPanelRow>
								<AtrcControlSelect
									label={__('Hide admin bar', 'brand-master')}
									wrapProps={{
										className: 'at-flx-grw-1',
									}}
									value={hideAdminBar.useRoles}
									isMulti={true}
									multiValType='array'
									options={userRolesOptions.all}
									onChange={(newVal) => {
										const newHideAdminBar = AtrcNestedObjUpdateByKey1({
											settings: hideAdminBar,
											key1: 'useRoles',
											val1: newVal,
										});
										dbUpdateSetting('hideAdminBar', newHideAdminBar);
									}}
								/>
							</AtrcPanelRow>
						) : null}
					</>
				) : null}
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Admin dashboard access', 'brand-master')}
				initialOpen={false}>
				<AtrcPanelRow className={classNames('at-m')}>
					<AtrcControlLink
						allowOn={true}
						allowTitle={false}
						allowTarget={false}
						label={__('Admin dashboard access', 'brand-master')}
						value={redirectAdminDashboard}
						onChange={(newVal) =>
							dbUpdateSetting('redirectAdminDashboard', newVal)
						}
						onProps={{
							label: __('Disable admin end access', 'brand-master'),
						}}
						urlProps={{
							label: __('Redirect url', 'brand-master'),
						}}
					/>
				</AtrcPanelRow>
				{redirectAdminDashboard.on ? (
					<AtrcPanelRow>
						<AtrcControlSelect
							label={__(
								'Restrict access to specific roles only',
								'brand-master'
							)}
							help={__(
								'By default, administrators have unrestricted access to the dashboard, whereas subscribers have denied access.',
								'brand-master'
							)}
							wrapProps={{
								className: 'at-flx-grw-1',
							}}
							value={redirectAdminDashboard.useRoles}
							isMulti={true}
							multiValType='array'
							options={userRolesOptions.excludeAdmin}
							onChange={(newVal) => {
								const newRedirect = AtrcNestedObjUpdateByKey1({
									settings: redirectAdminDashboard,
									key1: 'useRoles',
									val1: newVal,
								});
								dbUpdateSetting('redirectAdminDashboard', newRedirect);
							}}
						/>
					</AtrcPanelRow>
				) : null}
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Redirecting after login', 'brand-master')}
				initialOpen={false}>
				<AtrcControlLink
					allowOn={true}
					allowTitle={false}
					allowTarget={false}
					label={__('Redirecting after login', 'brand-master')}
					value={redirectLogin}
					onChange={(newVal) => dbUpdateSetting('redirectLogin', newVal)}
					onProps={{
						label: __('Enable redirection', 'brand-master'),
					}}
					urlProps={{
						label: __('Redirection URL', 'brand-master'),
					}}
				/>
			</AtrcPanelBody>

			<AtrcPanelBody
				title={__('Redirecting after logout', 'brand-master')}
				initialOpen={false}>
				<AtrcControlLink
					allowOn={true}
					allowTitle={false}
					allowTarget={false}
					label={__('Redirecting after logout', 'brand-master')}
					value={redirectLogout}
					onChange={(newVal) => dbUpdateSetting('redirectLogout', newVal)}
					onProps={{
						label: __('Enable redirection', 'brand-master'),
					}}
					urlProps={{
						label: __('Redirection URL', 'brand-master'),
					}}
				/>
			</AtrcPanelBody>
			<AtrcPanelBody
				title={__(
					'Redirecting after lost password (password recovery)',
					'brand-master'
				)}
				initialOpen={false}>
				<AtrcControlLink
					allowOn={true}
					allowTitle={false}
					allowTarget={false}
					label={__(
						'Redirecting after lost password (password recovery)',
						'brand-master'
					)}
					value={redirectLostPassword}
					onChange={(newVal) => dbUpdateSetting('redirectLostPassword', newVal)}
					onProps={{
						label: __('Enable redirection', 'brand-master'),
					}}
					urlProps={{
						label: __('Redirection URL', 'brand-master'),
					}}
				/>
			</AtrcPanelBody>
			<AtrcPanelBody
				title={__('Redirecting after registration', 'brand-master')}
				initialOpen={false}>
				<AtrcControlLink
					allowOn={true}
					allowTitle={false}
					allowTarget={false}
					label={__('Redirecting after registration', 'brand-master')}
					value={redirectRegistration}
					onChange={(newVal) => dbUpdateSetting('redirectRegistration', newVal)}
					onProps={{
						label: __('Enable redirection', 'brand-master'),
					}}
					urlProps={{
						label: __('Redirection URL', 'brand-master'),
					}}
				/>
			</AtrcPanelBody>
		</AtrcContent>
	);
};

const Documentation = () => {
	const data = useContext(AtrcReduxContextData);

	const { lsSettings, lsSaveSettings } = data;

	return (
		<AtrcWireFrameHeaderContentFooter
			headerRowProps={{
				className: classNames(AtrcPrefix('header-docs'), 'at-m'),
			}}
			renderHeader={
				<DocsTitle
					onClick={() => {
						const localStorageClone = cloneDeep(lsSettings);
						localStorageClone.bmSuDocs1 = !localStorageClone.bmSuDocs1;
						lsSaveSettings(localStorageClone);
					}}
				/>
			}
			renderContent={
				<>
					<AtrcPanelBody
						className={classNames(AtrcPrefix('m-0'))}
						title={__(
							'What role do utilities settings play in WordPress website customization?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'Utilities settings are instrumental in shaping your brand identity and customizing essential aspects and behaviors of both the frontend and login page of your WordPress website.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
					<AtrcPanelBody
						title={__(
							'What is the purpose of the utilities settings?',
							'brand-master'
						)}
						initialOpen={true}>
						<AtrcText
							tag='p'
							className={classNames(AtrcPrefix('m-0'), 'at-m')}>
							{__(
								'The utilities settings serve to enhance user experience and security. They offer functionalities such as hiding the admin bar for non-administrative users, customizing redirection after login, logout, password recovery, and registration, and restricting access to the wp-admin directory to bolster security measures.',
								'brand-master'
							)}
						</AtrcText>
					</AtrcPanelBody>
				</>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

const Settings = () => {
	const data = useContext(AtrcReduxContextData);
	const { lsSettings } = data;

	const { bmSuDocs1 } = lsSettings;

	return (
		<AtrcWireFrameHeaderContentFooter
			wrapProps={{
				className: classNames(AtrcPrefix('bg-white'), 'at-bg-cl'),
			}}
			renderHeader={
				<AtrcTitleTemplate1 title={__('Settings', 'brand-master')} />
			}
			renderContent={
				<AtrcWireFrameContentSidebar
					wrapProps={{
						allowContainer: true,
						type: 'fluid',
						tag: 'section',
					}}
					renderContent={<MainContent />}
					renderSidebar={!bmSuDocs1 ? <Documentation /> : null}
					contentProps={{
						contentCol: bmSuDocs1 ? 'at-col-12' : 'at-col-7',
					}}
					sidebarProps={{
						sidebarCol: 'at-col-5',
					}}
				/>
			}
			allowHeaderRow={false}
			allowHeaderCol={false}
			allowContentRow={false}
			allowContentCol={false}
		/>
	);
};

export default Settings;
